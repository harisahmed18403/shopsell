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
                                <select name="type" class="select select-bordered select-sm w-full" required>
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

                    <div class="overflow-x-auto">
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
                                        <td class="py-1">
                                            <div class="flex flex-col gap-1">
                                                <select :name="'items['+index+'][product_id]'" class="select select-bordered select-xs w-full" x-model="item.product_id" @change="updatePrice(index)">
                                                    <option value="">-- Manual/Custom --</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" :name="'items['+index+'][description]'" class="input input-bordered input-xs w-full" placeholder="Custom description/Repair details" x-model="item.description">
                                            </div>
                                        </td>
                                        <td class="text-center py-1">
                                            <input type="number" :name="'items['+index+'][quantity]'" class="input input-bordered input-xs w-16 text-center" x-model.number="item.quantity" min="1" required>
                                        </td>
                                        <td class="py-1">
                                            <div class="relative">
                                                <span class="absolute left-2 top-1 text-[10px] opacity-50">£</span>
                                                <input type="number" step="0.01" :name="'items['+index+'][price]'" class="input input-bordered input-xs w-full pl-5" x-model.number="item.price" min="0" required>
                                            </div>
                                        </td>
                                        <td class="font-bold py-1">
                                            £<span x-text="(item.quantity * item.price).toFixed(2)"></span>
                                        </td>
                                        <td class="text-center py-1">
                                            <button type="button" @click="removeItem(index)" class="btn btn-ghost btn-xs text-error p-0 h-6 w-6 min-h-0" x-show="items.length > 1">×</button>
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
                items: [{ product_id: '', description: '', quantity: 1, price: 0 }],
                products: @json($products),
                
                addItem() {
                    this.items.push({ product_id: '', description: '', quantity: 1, price: 0 });
                },
                
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                
                updatePrice(index) {
                    const productId = this.items[index].product_id;
                    if (productId) {
                        const product = this.products.find(p => p.id == productId);
                        if (product) {
                            this.items[index].price = product.sale_price;
                            this.items[index].description = product.name;
                        }
                    }
                },
                
                calculateTotal() {
                    return this.items.reduce((total, item) => total + (item.quantity * item.price), 0).toFixed(2);
                }
            }
        }
    </script>
</x-app-layout>
