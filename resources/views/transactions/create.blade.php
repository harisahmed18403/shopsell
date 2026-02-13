<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Transaction') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data="transactionForm()">
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Transaction Type -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Transaction Details</h3>
                        <div class="form-control w-full">
                            <label class="label"><span class="label-text">Type</span></label>
                            <select name="type" class="select select-bordered w-full" required>
                                <option value="sell">Sell</option>
                                <option value="buy">Buy</option>
                                <option value="repair">Repair</option>
                            </select>
                        </div>

                        <div class="form-control w-full mt-4">
                            <label class="label"><span class="label-text">Customer</span></label>
                            <select name="customer_id" class="select select-bordered w-full">
                                <option value="">Walk-in Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="font-bold text-lg mb-4">Summary</h3>
                        <div class="flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span>£<span x-text="calculateTotal()"></span></span>
                        </div>
                        <div class="card-actions justify-end mt-6">
                            <button type="submit" class="btn btn-primary w-full">Complete Transaction</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-lg">Items</h3>
                        <button type="button" @click="addItem()" class="btn btn-sm btn-outline btn-primary">Add Item</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th class="w-1/2">Product/Description</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td>
                                            <div class="flex flex-col gap-2">
                                                <select :name="'items['+index+'][product_id]'" class="select select-bordered select-sm w-full" x-model="item.product_id" @change="updatePrice(index)">
                                                    <option value="">-- Manual/Custom --</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" :name="'items['+index+'][description]'" class="input input-bordered input-sm w-full" placeholder="Custom description/Repair details" x-model="item.description">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" :name="'items['+index+'][quantity]'" class="input input-bordered input-sm w-16" x-model.number="item.quantity" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" :name="'items['+index+'][price]'" class="input input-bordered input-sm w-24" x-model.number="item.price" min="0" required>
                                        </td>
                                        <td class="font-bold">
                                            £<span x-text="(item.quantity * item.price).toFixed(2)"></span>
                                        </td>
                                        <td>
                                            <button type="button" @click="removeItem(index)" class="btn btn-ghost btn-xs text-error" x-show="items.length > 1">×</button>
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
