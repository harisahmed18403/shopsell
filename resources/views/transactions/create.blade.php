<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">New Transaction</h1>
        <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-sm">Cancel</a>
    </div>

    <div class="" x-data="transactionForm()">
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Transaction Type -->
                <div class="md:col-span-1 card bg-base-100 shadow-sm rounded-sm border border-base-300">
                    <div class="card-body p-4">
                        <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Transaction Type</h3>
                        <div class="form-control w-full">
                            <label class="label p-1"><span class="label-text text-xs">Type</span></label>
                            <select name="type" class="select select-bordered w-full" required x-model="type">
                                <option value="sell">Sell</option>
                                <option value="buy">Buy</option>
                                <option value="repair">Repair</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Customer Selection/Details -->
                <div class="md:col-span-2 card bg-base-100 shadow-sm rounded-sm border border-base-300">
                    <div class="card-body p-4">
                        <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Customer Details (Optional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="form-control w-full">
                                <label class="label p-1"><span class="label-text text-xs">Existing Customer</span></label>
                                <select name="customer_id" class="select select-bordered w-full">
                                    <option value="">Walk-in / New</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-control w-full">
                                <label class="label p-1"><span class="label-text text-xs">Full Name</span></label>
                                <input type="text" name="customer_name" class="input input-bordered w-full" placeholder="Customer Name" />
                            </div>
                            <div class="form-control w-full">
                                <label class="label p-1"><span class="label-text text-xs">Email Address</span></label>
                                <input type="email" name="customer_email" class="input input-bordered w-full" placeholder="email@example.com" />
                            </div>
                            <div class="form-control w-full">
                                <label class="label p-1"><span class="label-text text-xs">Phone Number</span></label>
                                <input type="text" name="customer_phone" class="input input-bordered w-full" placeholder="0123456789" />
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
                        <button type="submit" class="btn btn-success text-white w-full">Complete</button>
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

                    <div class="overflow-x-visible overflow-y-visible">
                        <table class="table w-full overflow-visible">
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
                                    <tr class="hover:bg-base-100">
                                        <td class="py-2 relative overflow-visible">
                                            <div class="flex flex-col gap-1 overflow-visible">
                                                <input type="hidden" :name="'items['+index+'][product_id]'" x-model="item.product_id">
                                                
                                                <!-- Search Input -->
                                                <div class="relative">
                                                    <input 
                                                        type="text" 
                                                        class="input input-bordered w-full" 
                                                        placeholder="Search product..." 
                                                        x-model="item.search"
                                                        @input.debounce.300ms="searchProduct(index)"
                                                        @focus="item.showResults = true"
                                                        @click.away="item.showResults = false"
                                                    >
                                                    <div x-show="item.loading" class="absolute right-2 top-3">
                                                        <span class="loading loading-spinner loading-xs"></span>
                                                    </div>
                                                </div>

                                                <!-- Search Results -->
                                                <div x-show="item.showResults && item.searchResults.length > 0" 
                                                     class="absolute z-[999] w-full mt-12 bg-base-100 shadow-2xl rounded-box border border-base-300 max-h-64 overflow-y-auto left-0"
                                                     style="display: none;">
                                                    <template x-for="res in item.searchResults" :key="res.id">
                                                        <div class="flex flex-col border-b border-base-200 last:border-0 hover:bg-base-200/50 transition-colors">
                                                            <!-- Main Product Row (Click to select base product) -->
                                                            <div @click="selectProduct(index, res)" class="flex items-center gap-2 p-2 cursor-pointer">
                                                                <div class="w-6 h-6 flex-shrink-0">
                                                                    <img :src="res.image_url" class="w-full h-full object-contain bg-white rounded border border-base-200">
                                                                </div>
                                                                <div class="text-[10px] font-bold truncate flex-1" x-text="res.name"></div>
                                                            </div>
                                                            <!-- Grade reference badges -->
                                                            <div class="flex flex-wrap gap-1 px-2 pb-2" x-show="res.variants && res.variants.length > 0">
                                                                <template x-for="(v, vIdx) in res.variants" :key="res.id + '-' + v.grade + '-' + vIdx">
                                                                    <button type="button" 
                                                                            @click="selectProduct(index, res, v)"
                                                                            class="badge badge-outline badge-xs py-2 hover:badge-primary cursor-pointer transition-colors flex gap-1 items-center">
                                                                        <span class="font-black" x-text="v.grade"></span>
                                                                        <span class="opacity-70" x-text="'£' + parseFloat(type === 'sell' ? v.sale : v.cash).toFixed(0)"></span>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <input type="text" :name="'items['+index+'][description]'" class="input input-bordered w-full" placeholder="Custom description/Repair details" x-model="item.description">
                                            </div>
                                        </td>
                                        <td class="text-center py-2 align-top">
                                            <input type="number" :name="'items['+index+'][quantity]'" class="input input-bordered w-24 text-center mt-1" x-model.number="item.quantity" min="1" required>
                                        </td>
                                        <td class="py-2 align-top">
                                            <div class="relative mt-1">
                                                <span class="absolute left-3 top-3 text-sm opacity-50">£</span>
                                                <input type="number" step="0.01" :name="'items['+index+'][price]'" class="input input-bordered w-full pl-7" x-model.number="item.price" min="0" required>
                                            </div>
                                        </td>
                                        <td class="font-bold py-2 align-top">
                                            <div class="mt-4">£<span x-text="(item.quantity * item.price).toFixed(2)"></span></div>
                                        </td>
                                        <td class="text-center py-2 align-top">
                                            <button type="button" @click="removeItem(index)" class="btn btn-ghost btn-xs text-error p-0 h-6 w-6 min-h-0 mt-3" x-show="items.length > 1">×</button>
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

                selectProduct(index, product, variant = null) {
                    this.items[index].product_id = product.id;
                    this.items[index].search = product.name + (variant ? ' ('+variant.grade+')' : '');
                    this.items[index].description = product.name + (variant ? ' - Grade ' + variant.grade : '');
                    this.items[index].showResults = false;
                    
                    // Do not auto-populate price as per user request, user will enter manually
                    // If you want to populate it as a starting point, uncomment below
                    /*
                    if (variant) {
                        this.items[index].price = (this.type === 'sell') ? variant.sale : variant.cash;
                    }
                    */
                },
                
                calculateTotal() {
                    return this.items.reduce((total, item) => total + (item.quantity * (item.price || 0)), 0).toFixed(2);
                }
            }
        }
    </script>
</x-app-layout>
