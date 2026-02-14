<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">New Transaction</h1>
        <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
    </div>

    <div class="" x-data="transactionForm()">
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Transaction Type -->
                <div class="md:col-span-2 card bg-base-100 shadow-sm rounded-sm border border-base-300">
                    <div class="card-body p-4">
                        <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Transaction Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-control w-full">
                                <label class="label p-1"><span class="label-text text-xs">Type</span></label>
                                <select name="type" class="select select-bordered select-sm w-full" required x-model="type">
                                    <option value="sell">Sell</option>
                                    <option value="buy">Buy</option>
                                    <option value="repair">Repair</option>
                                </select>
                            </div>

                            <div class="form-control w-full">
                                <label class="label p-1"><span class="label-text text-xs">Customer</span></label>
                                <select name="customer_id" class="select select-bordered select-sm w-full">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
                    <div class="card-body p-4">
                        <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Summary</h3>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-semibold opacity-70">Total Amount:</span>
                            <span class="text-2xl font-bold text-primary">£<span x-text="calculateTotal()"></span></span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-full">Complete</button>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300">
                <div class="card-body p-4">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="font-bold text-xs uppercase opacity-60">Items</h3>
                        <button type="button" @click="addItem()" class="btn btn-xs btn-outline btn-primary">Add Item</button>
                    </div>

                    <div class="overflow-x-auto overflow-visible">
                        <table class="table table-xs w-full">
                            <thead>
                                <tr class="bg-base-200">
                                    <th class="w-1/2">Product/Description</th>
                                    <th class="w-20 text-center">Qty</th>
                                    <th class="w-32">Price</th>
                                    <th class="w-32">Subtotal</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="py-1 relative">
                                            <div class="flex flex-col gap-1">
                                                <input type="hidden" :name="'items['+index+'][product_id]'" x-model="item.product_id">
                                                
                                                <!-- Search Input -->
                                                <div class="relative">
                                                    <input 
                                                        type="text" 
                                                        class="input input-bordered input-xs w-full" 
                                                        placeholder="Search product..." 
                                                        x-model="item.search"
                                                        @input.debounce.300ms="searchProduct(index)"
                                                        @focus="item.showResults = true"
                                                        @click.away="item.showResults = false"
                                                    >
                                                    <div x-show="item.loading" class="absolute right-2 top-1">
                                                        <span class="loading loading-spinner loading-xs scale-75"></span>
                                                    </div>
                                                </div>

                                                <!-- Search Results -->
                                                <div x-show="item.showResults && item.searchResults.length > 0" 
                                                     class="absolute z-[100] w-full mt-7 bg-base-100 shadow-2xl rounded-box border border-base-300 max-h-48 overflow-y-auto left-0"
                                                     style="display: none;">
                                                    <template x-for="res in item.searchResults" :key="res.id">
                                                        <button type="button" 
                                                                @click="selectProduct(index, res)"
                                                                class="w-full text-left px-2 py-1.5 hover:bg-base-200 flex items-center gap-2 border-b border-base-200 last:border-0">
                                                            <div class="w-6 h-6 flex-shrink-0">
                                                                <img :src="res.image_url" class="w-full h-full object-contain bg-white rounded border border-base-200">
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-[10px] font-bold truncate" x-text="res.name"></div>
                                                                <div class="text-[8px] opacity-60" x-text="'Grade ' + (res.grade || 'N/A')"></div>
                                                            </div>
                                                            <div class="text-[10px] font-bold text-primary">£<span x-text="parseFloat(res.sale_price).toFixed(0)"></span></div>
                                                        </button>
                                                    </template>
                                                </div>

                                                <input type="text" :name="'items['+index+'][description]'" class="input input-bordered input-xs w-full" placeholder="Custom description/Repair details" x-model="item.description">
                                            </div>
                                        </td>
                                        <td class="text-center py-1 align-top">
                                            <input type="number" :name="'items['+index+'][quantity]'" class="input input-bordered input-xs w-16 text-center mt-1" x-model.number="item.quantity" min="1" required>
                                        </td>
                                        <td class="py-1 align-top">
                                            <div class="relative mt-1">
                                                <span class="absolute left-2 top-1 text-[10px] opacity-50">£</span>
                                                <input type="number" step="0.01" :name="'items['+index+'][price]'" class="input input-bordered input-xs w-full pl-5" x-model.number="item.price" min="0" required>
                                            </div>
                                        </td>
                                        <td class="font-bold py-1 align-top">
                                            <div class="mt-2">£<span x-text="(item.quantity * item.price).toFixed(2)"></span></div>
                                        </td>
                                        <td class="text-center py-1 align-top">
                                            <button type="button" @click="removeItem(index)" class="btn btn-ghost btn-xs text-error p-0 h-6 w-6 min-h-0 mt-1" x-show="items.length > 1">×</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function transactionForm() {
            return {
                type: 'sell',
                items: [{ 
                    product_id: '', 
                    description: '', 
                    quantity: 1, 
                    price: 0, 
                    search: '', 
                    searchResults: [], 
                    showResults: false, 
                    loading: false 
                }],
                
                addItem() {
                    this.items.push({ 
                        product_id: '', 
                        description: '', 
                        quantity: 1, 
                        price: 0, 
                        search: '', 
                        searchResults: [], 
                        showResults: false, 
                        loading: false 
                    });
                },
                
                removeItem(index) {
                    this.items.splice(index, 1);
                },

                async searchProduct(index) {
                    const query = this.items[index].search;
                    if (query.length < 2) {
                        this.items[index].searchResults = [];
                        return;
                    }

                    this.items[index].loading = true;
                    try {
                        const response = await fetch("{{ route('products.search') }}?q=" + encodeURIComponent(query));
                        this.items[index].searchResults = await response.json();
                        this.items[index].showResults = true;
                    } catch (error) {
                        console.error('Search failed:', error);
                    } finally {
                        this.items[index].loading = false;
                    }
                },

                selectProduct(index, product) {
                    this.items[index].product_id = product.id;
                    this.items[index].search = product.name + (product.grade ? ' ('+product.grade+')' : '');
                    this.items[index].description = product.name;
                    this.items[index].showResults = false;
                    
                    // Set price based on transaction type
                    if (this.type === 'sell') {
                        this.items[index].price = product.sale_price || 0;
                    } else if (this.type === 'buy') {
                        this.items[index].price = product.cash_price || 0;
                    } else {
                        this.items[index].price = 0;
                    }
                },
                
                calculateTotal() {
                    return this.items.reduce((total, item) => total + (item.quantity * (item.price || 0)), 0).toFixed(2);
                }
            }
        }
    </script>
</x-app-layout>
