<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Add to Inventory</h1>
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
            <div class="card-body p-4" x-data="inventoryForm()">
                <!-- Product Search Component -->
                <div class="mb-6">
                    <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Search Catalog Product</span></label>
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            @input.debounce.300ms="search()"
                            placeholder="Type to search products..." 
                            class="input input-bordered input-sm w-full" 
                        />
                        <div x-show="showDropdown" class="absolute z-50 w-full mt-1 bg-base-100 shadow-xl border border-base-300 rounded-md max-h-60 overflow-y-auto">
                            <template x-for="product in results" :key="product.id">
                                <button @click="selectProduct(product)" class="w-full text-left p-2 hover:bg-base-200 border-b border-base-100 last:border-0 flex items-center gap-3">
                                    <img :src="product.image_url" class="w-8 h-8 object-contain bg-white rounded border border-base-200" />
                                    <div>
                                        <div class="text-xs font-bold" x-text="product.name"></div>
                                        <div class="text-[10px] opacity-50" x-text="'ID: ' + product.id"></div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <form action="{{ route('inventory.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" :value="selectedProduct?.id" required />
                    
                    <div class="form-control w-full">
                        <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Selected Product</span></label>
                        <input type="text" :value="selectedProduct?.name" class="input input-bordered input-sm w-full bg-base-200" readonly placeholder="Search and select above..." required />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">IMEI / Serial Number</span></label>
                            <input type="text" name="imei" class="input input-bordered input-sm" value="{{ old('imei') }}" placeholder="Enter unique identifier" />
                        </div>
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Condition</span></label>
                            <input type="text" name="condition" class="input input-bordered input-sm" placeholder="e.g. Mint, Good, Cracked" value="{{ old('condition') }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Purchase Price</span></label>
                            <input type="number" step="0.01" name="purchase_price" class="input input-bordered input-sm" value="{{ old('purchase_price') }}" placeholder="Cost to you" />
                        </div>
                        <div class="form-control">
                            <label class="label p-1"><span class="label-text text-xs font-bold uppercase opacity-60">Listing Sale Price</span></label>
                            <input type="number" step="0.01" name="sale_price" class="input input-bordered input-sm" :value="selectedProduct?.sale_price" placeholder="Retail price" />
                        </div>
                    </div>

                    <div class="card-actions justify-end mt-6">
                        <button type="submit" class="btn btn-primary btn-sm px-8" :disabled="!selectedProduct">Add to Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function inventoryForm() {
            return {
                searchQuery: '',
                results: [],
                showDropdown: false,
                selectedProduct: null,

                async search() {
                    if (this.searchQuery.length < 2) {
                        this.results = [];
                        this.showDropdown = false;
                        return;
                    }

                    try {
                        const response = await fetch("{{ route('products.search') }}?q=" + encodeURIComponent(this.searchQuery));
                        this.results = await response.json();
                        this.showDropdown = true;
                    } catch (error) {
                        console.error('Search failed:', error);
                    }
                },

                selectProduct(product) {
                    this.selectedProduct = product;
                    this.searchQuery = product.name;
                    this.showDropdown = false;
                }
            }
        }
    </script>
</x-app-layout>
