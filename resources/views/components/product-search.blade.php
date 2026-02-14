<div x-data="productSearch()" class="relative w-full max-w-xl mx-auto">
    <div class="form-control">
        <div class="relative">
            <input 
                type="text" 
                x-model="query" 
                @input.debounce.300ms="search()"
                @keydown.escape="showDropdown = false"
                @click.away="showDropdown = false"
                placeholder="Search products, prices, grades..." 
                class="input input-bordered input-sm w-full" 
            />
            <!-- Loading State -->
            <div x-show="loading" class="absolute right-3 top-2">
                <span class="loading loading-spinner loading-xs text-primary"></span>
            </div>
        </div>
    </div>

    <!-- Dropdown -->
    <div 
        x-show="showDropdown && results.length > 0" 
        x-transition
        class="absolute z-50 w-full mt-2 bg-base-100 shadow-2xl rounded-box border border-base-300 max-h-[400px] overflow-y-auto overflow-x-hidden"
    >
        <div class="p-1">
            <template x-for="product in results" :key="product.id">
                <a :href="product.url" class="flex items-center gap-2 p-1.5 hover:bg-base-200 rounded-lg transition-colors border-b border-base-100 last:border-0">
                    <div class="avatar flex-shrink-0">
                        <div class="w-8 h-8 rounded-md overflow-hidden">
                            <img :src="product.image_url" :alt="product.name" loading="lazy" class="object-cover w-full h-full" />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-[11px] truncate leading-tight" x-text="product.cex_name || product.name"></div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="badge badge-outline text-[8px] h-3.5 px-1 uppercase" x-text="'Grade ' + (product.grade || 'N/A')"></span>
                        </div>
                    </div>

                    <div class="text-right flex flex-col gap-0 flex-shrink-0">
                        <!-- Shop Prices -->
                        <div class="flex gap-1 justify-end text-[7px] uppercase opacity-60 font-bold">Shop</div>
                        <div class="flex gap-1.5 justify-end items-center">
                            <div class="text-[9px] font-bold text-primary" x-text="'£' + parseFloat(product.sale_price).toFixed(0)"></div>
                            <div class="text-[9px] font-bold text-error" x-text="'£' + parseFloat(product.cash_price).toFixed(0)"></div>
                        </div>
                        
                        <!-- CeX Prices -->
                        <div x-show="product.cex_sale !== null || product.cex_cash !== null" class="mt-0.5">
                            <div class="flex gap-1 justify-end text-[7px] uppercase opacity-60 font-bold text-secondary">CeX</div>
                            <div class="flex gap-1.5 justify-end items-center">
                                <div class="text-[9px] text-secondary font-semibold" x-text="'£' + parseFloat(product.cex_sale || 0).toFixed(0)"></div>
                                <div class="text-[9px] text-error font-semibold" x-text="'£' + parseFloat(product.cex_cash || 0).toFixed(0)"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>

<script>
    function productSearch() {
        return {
            query: '',
            results: [],
            showDropdown: false,
            loading: false,

            async search() {
                if (this.query.length < 2) {
                    this.results = [];
                    this.showDropdown = false;
                    return;
                }

                this.loading = true;
                try {
                    const response = await fetch("{{ route('products.search') }}?q=" + encodeURIComponent(this.query));
                    this.results = await response.json();
                    this.showDropdown = true;
                } catch (error) {
                    console.error('Search failed:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
