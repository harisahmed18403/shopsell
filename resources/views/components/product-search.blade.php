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
        class="absolute z-50 w-full mt-2 bg-base-100 shadow-2xl rounded-box border border-base-300 max-h-[500px] overflow-y-auto overflow-x-hidden"
    >
        <div class="p-1">
            <template x-for="product in results" :key="product.id">
                <a :href="product.url" class="flex flex-col gap-2 p-2 hover:bg-base-200 rounded-lg transition-colors border-b border-base-100 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="avatar flex-shrink-0">
                            <div class="w-10 h-10 rounded-md overflow-hidden bg-white border border-base-200">
                                <img :src="product.image_url" :alt="product.name" loading="lazy" class="object-contain w-full h-full" />
                            </div>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-xs truncate" x-text="product.name"></div>
                        </div>
                    </div>

                    <!-- Grades Row -->
                    <div class="flex flex-wrap gap-2 px-1 pb-1">
                        <template x-for="v in product.variants" :key="v.grade">
                            <div class="flex flex-col items-center bg-base-100 border border-base-300 rounded p-1 min-w-[60px] shadow-sm">
                                <span class="text-[9px] font-black opacity-50 uppercase" x-text="'Grade ' + v.grade"></span>
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] font-bold text-primary" x-text="'S: £' + parseFloat(v.sale).toFixed(0)"></span>
                                    <span class="text-[10px] font-bold text-error" x-text="'C: £' + parseFloat(v.cash).toFixed(0)"></span>
                                </div>
                            </div>
                        </template>
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
