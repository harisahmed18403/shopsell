import requests
import traceback
import threading
import time
import os
from app import app, db
from app.models import SuperCat, ProductLine, Category, Product, CexProduct, ProductVariant
from urllib.parse import urlencode
from concurrent.futures import ThreadPoolExecutor, as_completed

from app.status_store import refresh_status

class RefreshCex():
    apiUrl = f"https://wss2.cex.{app.config['CEX_COUNTRY_CODE']}.webuy.io/v3"
    websiteApiUrl = "https://search.webuy.io/1/indexes/*/queries"
    debug_log_path = os.path.join(os.path.dirname(__file__), "../../../logs/refresh_debug.log")

    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/120.0.0.0 Safari/537.36",
        "Accept": "application/json"
    }

    def _log(self, message):
        timestamp = time.strftime("%Y-%m-%d %H:%M:%S")
        formatted_msg = f"[{timestamp}] {message}"
        print(formatted_msg)
        refresh_status["logs"].append(formatted_msg)
        try:
            os.makedirs(os.path.dirname(self.debug_log_path), exist_ok=True)
            with open(self.debug_log_path, "a") as f:
                f.write(formatted_msg + "\n")
        except Exception as e:
            print(f"Failed to write to debug log: {str(e)}")

    def _safe_commit(self, session, context_msg=""):
        """Attempts to commit with retries to handle SQLite locking."""
        max_retries = 5
        base_delay = 1.0
        for i in range(max_retries):
            try:
                session.commit()
                return True
            except Exception as e:
                session.rollback()
                error_str = str(e).lower()
                if "locked" in error_str or "busy" in error_str:
                    wait_time = base_delay * (i + 1)
                    self._log(f"Database locked ({context_msg}). Retrying in {wait_time}s... (Attempt {i+1}/{max_retries})")
                    time.sleep(wait_time)
                else:
                    self._log(f"Commit failed ({context_msg}): {str(e)}")
                    return False
        self._log(f"Failed to commit after {max_retries} attempts ({context_msg}).")
        return False

    def fetchFromApi(self, endpoint, responseKey, params = None):
        try:
            response = requests.get(self.apiUrl + endpoint, timeout=30, headers=self.headers, params=params)
            response.raise_for_status()
            json = response.json()
            if(json == None or json["response"] == None or json["response"]["data"] == None or json["response"]["data"][responseKey] == None):
                return []
            return json["response"]["data"][responseKey]
        except Exception as e:
            self._log(f"API Error fetching {endpoint}: {str(e)}")
            return []

    def refreshSuperCats(self):
        with app.app_context():
            for row in self.fetchFromApi("/supercats", "superCats"):
                superCat = db.session.get(SuperCat, row["superCatId"])
                if not superCat:
                    superCat = SuperCat(id=row["superCatId"], name=row["superCatFriendlyName"])
                    db.session.add(superCat)
                else:
                    superCat.name = row["superCatFriendlyName"]
            self._safe_commit(db.session, "SuperCats Refresh")

    # Product Lines
    def refreshProductLines(self):
        with app.app_context():
            for row in self.fetchFromApi("/productlines", "productLines"):
                productLine = db.session.get(ProductLine, row["productLineId"])
                if not productLine:
                    productLine = ProductLine(
                        id=row["productLineId"], 
                        name=row["productLineName"],
                        super_cat_id=row["superCatId"]
                    )
                    db.session.add(productLine)
                else:
                    productLine.super_cat_id = row["superCatId"]
                    productLine.name = row["productLineName"]
            self._safe_commit(db.session, "ProductLines Refresh")

    # Categories
    def refreshCategories(self):
        with app.app_context():
            # Fetch all IDs and flatten the list
            productLines = ProductLine.query.all()

            for pl in productLines:
                params = {
                    "productLineIds": f"[{pl.id}]"
                }
                response = self.fetchFromApi("/categories", "categories", params)                  
                for row in response:
                    category = db.session.get(Category, row["categoryId"])
                    if not category:
                        category = Category(
                            id=row["categoryId"], 
                            name=row["categoryFriendlyName"]
                        )
                        db.session.add(category)
                    else:
                        category.name = row["categoryFriendlyName"]

                    if pl and pl not in category.product_lines:
                        category.product_lines.append(pl)
                        
                self._safe_commit(db.session, f"Categories for Line {pl.id}")

    def refreshCategory(self, category_id, headers, limit=None):
        """Processes a single category. Runs in a background thread."""
        global refresh_status
        
        with app.app_context():
            session = db.session
            processed_count = 0
            
            try:
                self._log(f"Starting Category ID: {category_id}")
                
                params_dict = {
                    "clickAnalytics": "false",
                    "filters": f"categoryId:{category_id}",
                    "hitsPerPage": "100",
                    "page": "1",
                    "query": ""
                }

                page = 1
                while True:
                    if not refresh_status["is_running"]: break
                    if limit and processed_count >= limit: break
                    
                    params_dict["page"] = str(page)
                    params_str = urlencode(params_dict)

                    payload = {
                        "requests": [
                            {
                                "indexName": "prod_cex_uk",
                                "params": params_str
                            }
                        ]
                    }

                    try:
                        response = requests.post(self.websiteApiUrl, json=payload, headers=headers, timeout=10)
                        response.raise_for_status()
                        data = response.json()
                    except Exception as e:
                        self._log(f"Error fetching page {page} for cat {category_id}: {str(e)}")
                        break

                    if "results" not in data or not data["results"]:
                        self._log(f"No results in data for cat {category_id}: {str(data)}")
                        break

                    results = data["results"][0]
                    if not results.get("hits"):
                        self._log(f"No hits for cat {category_id} page {page}. Total results: {results.get('nbHits', 0)}")
                        break

                    for row in results["hits"]:
                        if not refresh_status["is_running"]: break
                        if limit and processed_count >= limit: break
                        
                        try:
                            # Use boxName directly, no highlighting result in raw hit often
                            item_name = row.get("boxName", "Unknown Item")
                            refresh_status["current_item"] = item_name

                            # Grade extraction
                            grade = None
                            grade_list = row.get("Grade", [])
                            if grade_list and len(grade_list) > 0:
                                grade = grade_list[0]
                            
                            if not grade:
                                name_upper = item_name.upper()
                                for g in ["A", "B", "C"]:
                                    if any(name_upper.endswith(p + g) for p in [" ", "/", ", ", "-"]):
                                        grade = g
                                        break

                            clean_name = item_name
                            if grade:
                                for pattern in [f" {grade}", f", {grade}", f"/{grade}", f"- {grade}"]:
                                    if clean_name.endswith(pattern):
                                        clean_name = clean_name[:-len(pattern)].strip()
                                        break
                            
                            # Use filter().first() but be mindful of name matches
                            master_product = Product.query.filter_by(name=clean_name).first()
                            if not master_product:
                                master_product = Product(
                                    name=clean_name,
                                    category_id=category_id,
                                    image_path=row.get("imageUrls", {}).get("medium") if row.get("imageUrls") else None
                                )
                                session.add(master_product)
                                session.flush()

                            cexProduct = CexProduct.query.filter_by(cex_id=row["objectID"]).first()
                            
                            if not cexProduct:
                                # Create a new variant
                                variant = ProductVariant(
                                    name=item_name,
                                    product_id=master_product.id,
                                    cash_price=row.get("cashPriceCalculated", row.get("cashPrice", 0)),
                                    voucher_price=row.get("exchangePriceCalculated", row.get("exchangePrice", 0)),
                                    sale_price=row.get("sellPrice", 0),
                                    image_path=row.get("imageUrls", {}).get("medium") if row.get("imageUrls") else None,
                                    grade=grade
                                )
                                session.add(variant)
                                session.flush()

                                cexProduct = CexProduct(cex_id=row["objectID"], variant_id=variant.id)
                                session.add(cexProduct)
                            else:
                                variant = cexProduct.variant
                                if variant:
                                    variant.cash_price = row.get("cashPriceCalculated", row.get("cashPrice", variant.cash_price))
                                    variant.voucher_price = row.get("exchangePriceCalculated", row.get("exchangePrice", variant.voucher_price))
                                    variant.sale_price = row.get("sellPrice", variant.sale_price)
                                    variant.grade = grade
                                    variant.product_id = master_product.id

                            if not master_product.image_path and variant and variant.image_path:
                                master_product.image_path = variant.image_path
                            
                            processed_count += 1
                            if processed_count % 10 == 0:
                                self._log(f"Category {category_id}: Processed {processed_count} items...")
                        except Exception as item_err:
                            self._log(f"Error item {row.get('objectID')}: {str(item_err)}")
                            continue 

                    # Commit once per page
                    self._safe_commit(session, f"Category {category_id} Page {page}")

                    if results["page"] >= results["nbPages"]:
                        break
                    page += 1
            except Exception as e:
                self._log(f"Error in category {category_id}: {str(e)}")

    def refreshSpecificProducts(self, product_ids):
        """Refreshes a specific list of product IDs."""
        global refresh_status
        
        with app.app_context():
            session = db.session
            try:
                products = Product.query.filter(Product.id.in_(product_ids)).all()
                self._log(f"Refreshing {len(products)} specific products.")
                
                count = 0
                for product in products:
                    if not refresh_status["is_running"]: break
                    
                    refresh_status["current_item"] = product.name
                    
                    for variant in product.variants:
                        cex_product = variant.cex_product
                        if not cex_product:
                            continue
                            
                        try:
                            boxUrl = f"/boxes/{cex_product.cex_id}/detail"
                            boxDetails = self.fetchFromApi(boxUrl, 'boxDetails')
                            if not boxDetails: continue
                            boxDetails = boxDetails[0]
                            
                            variant.cash_price = boxDetails["cashPrice"]
                            variant.voucher_price = boxDetails.get("exchangePrice")
                            variant.sale_price = boxDetails.get("sellPrice")
                            variant.image_path = boxDetails.get("imageUrls", {}).get("medium") if boxDetails.get("imageUrls") else variant.image_path
                            
                            # Update grade if it changed or was missing
                            grade = None
                            if boxDetails.get("boxName"):
                                item_name = boxDetails["boxName"]
                                name_upper = item_name.upper()
                                for g in ["A", "B", "C"]:
                                    if any(name_upper.endswith(p + g) for p in [" ", "/", ", ", "-"]):
                                        grade = g
                                        break
                            if grade:
                                variant.grade = grade
                        except Exception as var_err:
                            self._log(f"Error variant {variant.id}: {str(var_err)}")
                            continue
                    
                    count += 1
                    if count % 10 == 0:
                        self._safe_commit(session, f"Specific Products Batch {count}")
                
                # Final commit
                self._safe_commit(session, "Specific Products Final")
            except Exception as e:
                self._log(f"Error in specific products refresh: {str(e)}")

    def refreshProducts(self, category_ids=None, product_line_ids=None, product_ids=None, limit_per_category=None):
        global refresh_status
        refresh_status["is_running"] = True
        refresh_status["logs"] = []
        
        # Clear debug log
        try:
            os.makedirs(os.path.dirname(self.debug_log_path), exist_ok=True)
            with open(self.debug_log_path, "w") as f:
                f.write(f"--- Refresh Started at {time.strftime('%Y-%m-%d %H:%M:%S')} ---\n")
        except:
            pass

        try:
            # 1. Always refresh taxonomy first
            self._log("Refreshing CEX Taxonomy (Supercats, Product Lines, Categories)...")
            self.refreshSuperCats()
            self.refreshProductLines()
            self.refreshCategories()
            self._log("Taxonomy refreshed.")

            with app.app_context():
                headers = {
                    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                    "Content-Type": "application/json",
                    "X-Algolia-API-Key": app.config['CEX_ALGOLIA_API_KEY'],
                    "X-Algolia-Application-Id": app.config['CEX_ALGOLIA_APP_ID'],
                    "Accept": "application/json",
                    "Origin": "https://uk.webuy.com",
                    "Referer": "https://uk.webuy.com/"
                }

                # Handle specific product IDs if provided
                if product_ids:
                    self.refreshSpecificProducts(product_ids)

                all_category_ids = set()
                if category_ids:
                    all_category_ids.update(category_ids)
                
                if product_line_ids:
                    for pl_id in product_line_ids:
                        pl = ProductLine.query.get(pl_id)
                        if pl:
                            all_category_ids.update([c.id for c in pl.categories])
                
                # If nothing selected at all (no product_ids, no category_ids, no product_line_ids), 
                # default to Phones (106)
                if not all_category_ids and not product_ids:
                    pl_106 = ProductLine.query.get(106)
                    if pl_106:
                        all_category_ids.update([c.id for c in pl_106.categories])

                if all_category_ids:
                    self._log(f"Targeting {len(all_category_ids)} categories for sync.")
                    
                    # For SQLite, use max_workers=1 to prevent "database is locked" errors
                    # while still benefiting from background execution.
                    with ThreadPoolExecutor(max_workers=1) as executor:
                        futures = [executor.submit(self.refreshCategory, cid, headers, limit=limit_per_category) for cid in all_category_ids]
                        
                        for future in as_completed(futures):
                            try:
                                future.result()
                            except Exception as thread_exc:
                                self._log(f"Thread failed: {str(thread_exc)}")

        except Exception as e:
            err_msg = f"Fatal Refresh Error: {str(e)}\n{traceback.format_exc()}"
            self._log(err_msg)
        finally:
            refresh_status["is_running"] = False
            refresh_status["current_item"] = "Done"
            self._log("Refresh complete.")
