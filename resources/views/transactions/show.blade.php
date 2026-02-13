<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Transaction Details #{{ $transaction->id }}
            </h2>
            <div class="flex gap-2">
                <button onclick="window.print()" class="btn btn-ghost btn-sm">Print Receipt</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Details -->
            <div class="md:col-span-2 card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Items</h3>
                    <table class="table w-full mb-6">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->items as $item)
                                <tr>
                                    <td>
                                        <div class="font-bold">{{ $item->product?->name ?? 'Custom Item' }}</div>
                                        <div class="text-xs opacity-60">{{ $item->description }}</div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>£{{ number_format($item->price, 2) }}</td>
                                    <td class="text-right">£{{ number_format($item->quantity * $item->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        tfoot>
                            <tr class="font-bold text-lg">
                                <td colspan="3" class="text-right">Total:</td>
                                <td class="text-right">£{{ number_format($transaction->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Customer & Meta -->
            <div class="card bg-base-100 shadow h-fit">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">Information</h3>
                    
                    <div class="mb-4">
                        <label class="text-xs opacity-60 uppercase font-bold">Type</label>
                        <div class="capitalize">{{ $transaction->type }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs opacity-60 uppercase font-bold">Status</label>
                        <div><span class="badge badge-success">{{ $transaction->status }}</span></div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs opacity-60 uppercase font-bold">Date</label>
                        <div>{{ $transaction->created_at->format('d M Y, H:i') }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="text-xs opacity-60 uppercase font-bold">Served By</label>
                        <div>{{ $transaction->user->name }}</div>
                    </div>

                    <div class="mt-6">
                        <h4 class="font-bold border-b pb-1 mb-2">Customer</h4>
                        @if($transaction->customer)
                            <div>{{ $transaction->customer->name }}</div>
                            <div class="text-sm">{{ $transaction->customer->phone }}</div>
                            <div class="text-sm">{{ $transaction->customer->email }}</div>
                        @else
                            <div class="italic text-sm">Walk-in Customer</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
