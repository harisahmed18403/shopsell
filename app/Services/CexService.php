<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SuperCategory;
use App\Models\ProductLine;
use App\Models\Category;
use App\Models\Product;
use App\Models\CexProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CexService
{
    protected string $countryCode;
    protected string $apiUrl;
    protected string $websiteApiUrl;
    protected string $algoliaAppId;
    protected string $algoliaApiKey;
    protected array $headers;

    public function __construct()
    {
        $this->countryCode = config('services.cex.country_code', 'uk');
        $this->apiUrl = "https://wss2.cex.{$this->countryCode}.webuy.io/v3";
        $this->websiteApiUrl = "https://search.webuy.io/1/indexes/*/queries";
        $this->algoliaAppId = config('services.cex.algolia_app_id');
        $this->algoliaApiKey = config('services.cex.algolia_api_key');
        
        $this->headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
        ];
    }

    public function fetchFromRestApi(string $endpoint, array $params = [])
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->timeout(30)
                ->get($this->apiUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json()['response']['data'] ?? [];
            }
            
            Log::error("CeX REST API Error: " . $response->status(), ['body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error("CeX REST API Exception: " . $e->getMessage());
            return [];
        }
    }

    public function searchProducts(string $query, array $filters = [], int $page = 0, int $hitsPerPage = 100)
    {
        $params_dict = [
            "clickAnalytics" => "false",
            "hitsPerPage" => (string)$hitsPerPage,
            "page" => (string)$page,
            "query" => $query
        ];

        if (!empty($filters)) {
            $params_dict['filters'] = implode(' AND ', $filters);
        }

        $params_str = http_build_query($params_dict);

        $payload = [
            "requests" => [
                [
                    "indexName" => "prod_cex_" . $this->countryCode,
                    "params" => $params_str
                ]
            ]
        ];

        try {
            $response = Http::withHeaders(array_merge($this->headers, [
                "Content-Type" => "application/json",
                "X-Algolia-API-Key" => $this->algoliaApiKey,
                "X-Algolia-Application-Id" => $this->algoliaAppId,
                "Origin" => "https://{$this->countryCode}.webuy.com",
                "Referer" => "https://{$this->countryCode}.webuy.com/"
            ]))->post($this->websiteApiUrl, $payload);

            if ($response->successful()) {
                return $response->json()['results'][0] ?? [];
            }

            Log::error("CeX Algolia API Error: " . $response->status(), ['body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error("CeX Algolia API Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Synchronize the entire taxonomy for Phones.
     */
    public function syncPhoneTaxonomy()
    {
        Log::info("Syncing Phone Taxonomy from CeX...");

        // 1. Super Category 'Phones' (CeX ID 4)
        $superCat = SuperCategory::updateOrCreate(
            ['id' => 4],
            ['name' => 'Phones']
        );

        // 2. Product Lines for Phones
        $plData = $this->fetchFromRestApi("/productlines");
        $productLines = $plData['productLines'] ?? [];

        foreach ($productLines as $pl) {
            if ($pl['superCatId'] != 4) continue;

            $productLine = ProductLine::updateOrCreate(
                ['id' => $pl['productLineId']],
                [
                    'name' => $pl['productLineName'],
                    'super_category_id' => $superCat->id
                ]
            );

            // 3. Categories for each Product Line
            $catData = $this->fetchFromRestApi("/categories", ["productLineIds" => "[{$pl['productLineId']}]"]);
            $categories = $catData['categories'] ?? [];

            foreach ($categories as $cat) {
                Category::updateOrCreate(
                    ['id' => $cat['categoryId']],
                    [
                        'name' => $cat['categoryFriendlyName'],
                        'product_line_id' => $productLine->id
                    ]
                );
            }
        }

        Log::info("Phone Taxonomy sync completed.");
    }

    public function syncAllPhones(?int $limitPerCategory = null)
    {
        $this->syncPhoneTaxonomy();

        // Include all relevant phone product lines:
        // 113: iPhones, 114: Android, 83: Smartphones, 33: Other Phones
        $categories = Category::whereIn('product_line_id', [113, 114, 83, 33])->get();
        $totalSynced = 0;

        Log::info("Starting full sync for " . $categories->count() . " categories.");

        foreach ($categories as $category) {
            $totalSynced += $this->syncCategoryProducts($category->id, $limitPerCategory);
        }

        return $totalSynced;
    }

    public function syncCategoryProducts(int $categoryId, ?int $limit = null)
    {
        Log::info("Syncing products for Category ID: $categoryId (Limit: " . ($limit ?? 'Unlimited') . ")");
        
        $page = 0;
        $processedCount = 0;
        
        while (true) {
            if ($limit !== null && $processedCount >= $limit) break;

            $results = $this->searchProducts("", ["categoryId:$categoryId"], $page, 100);
            $hits = $results['hits'] ?? [];
            
            if (empty($hits)) break;

            foreach ($hits as $hit) {
                if ($limit !== null && $processedCount >= $limit) break;
                
                if ($this->updateOrCreateCexProduct($hit)) {
                    $processedCount++;
                }
            }

            if ($page >= ($results['nbPages'] ?? 0) - 1) break;
            $page++;
        }
        
        return $processedCount;
    }

    protected function updateOrCreateCexProduct(array $hit, ?Product $targetProduct = null)
    {
        $cexId = $hit['objectID'];
        $itemName = $hit['boxName'];
        $imageUrl = $hit['imageUrls']['medium'] ?? $hit['imageUrls']['small'] ?? null;
        $categoryId = $hit['categoryId'];
        
        $grade = null;
        if (!empty($hit['Grade']) && is_array($hit['Grade'])) {
            $grade = $hit['Grade'][0];
        }

        $cashPrice = $hit['cashPriceCalculated'] ?? $hit['cashPrice'] ?? 0;
        $voucherPrice = $hit['exchangePriceCalculated'] ?? $hit['exchangePrice'] ?? 0;
        $salePrice = $hit['sellPrice'] ?? 0;

        $product = $targetProduct;

        if (!$product) {
            $cleanName = $this->cleanName($itemName, $grade);
            
            // Look for existing global product
            $product = Product::where('name', $cleanName)
                ->whereNull('organization_id')
                ->first();
            
            if (!$product) {
                // Check if category exists locally
                $localCategory = Category::find($categoryId);
                if (!$localCategory) {
                    // Try to fetch taxonomy if missing? Or just skip for now.
                    return false; 
                }

                // Create a global product
                $product = Product::create([
                    'name' => $cleanName,
                    'category_id' => $localCategory->id,
                    'organization_id' => null, // Global
                    'sale_price' => $salePrice, // Default price from CeX
                    'cash_price' => $cashPrice,
                    'voucher_price' => $voucherPrice,
                    'grade' => $grade,
                    'quantity' => 0,
                ]);
            }
        }

        if ($product) {
            CexProduct::updateOrCreate(
                ['cex_id' => $cexId],
                [
                    'product_id' => $product->id,
                    'name' => $itemName,
                    'cash_price' => $cashPrice,
                    'sale_price' => $salePrice,
                    'voucher_price' => $voucherPrice,
                    'grade' => $grade,
                    'image_url' => $imageUrl,
                    'last_synced_at' => Carbon::now(),
                ]
            );
            return true;
        }
        return false;
    }

    protected function cleanName(string $name, ?string $grade)
    {
        // CeX names often have "Apple ", "Samsung ", then model, then specs, then grade.
        // We want a clean model name for our 'Product' table.
        // Variant info remains in 'CexProduct'.
        
        $clean = $name;
        
        // Remove grade patterns
        if ($grade) {
            $patterns = [" $grade", ", $grade", "/$grade", "- $grade"];
            foreach ($patterns as $pattern) {
                if (str_ends_with(strtoupper($clean), strtoupper($pattern))) {
                    $clean = trim(substr($clean, 0, -strlen($pattern)));
                    break;
                }
            }
        }

        // Remove ", Unlocked", ", EE", etc. (Network info)
        $clean = preg_replace('/, [A-Z0-9 ]+$/i', '', $clean);
        
        return trim($clean);
    }
}
