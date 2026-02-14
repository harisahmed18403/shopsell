<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-lg font-bold uppercase tracking-wider opacity-70">Transaction #{{ $transaction->id }}</h1>
        <div class="flex gap-2">
            <button onclick="window.print()" class="btn btn-ghost btn-sm">Print</button>
            <a href="{{ route('transactions.index') }}" class="btn btn-ghost btn-sm">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Details -->
        <div class="md:col-span-2 card bg-base-100 shadow-sm rounded-sm border border-base-300">
            <div class="card-body p-4">
                <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Items</h3>
                <table class="table table-xs w-full mb-4">
                    <thead>
                        <tr class="bg-base-200">
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th>Price</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->items as $item)
                            <tr class="hover">
                                <td>
                                    <div class="font-bold text-[11px]">{{ $item->product?->name ?? 'Custom Item' }}</div>
                                    <div class="text-[10px] opacity-60">{{ $item->description }}</div>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td>£{{ number_format($item->price, 2) }}</td>
                                <td class="text-right font-semibold">£{{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-base-200">
                            <td colspan="3" class="text-right uppercase text-[10px] tracking-wider opacity-70">Total Amount</td>
                            <td class="text-right text-base text-primary">£{{ number_format($transaction->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Customer & Meta -->
        <div class="card bg-base-100 shadow-sm rounded-sm border border-base-300 h-fit">
            <div class="card-body p-4">
                <h3 class="font-bold text-xs uppercase opacity-60 mb-4 border-b pb-2">Information</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-3">
                        <label class="text-[10px] opacity-60 uppercase font-bold block mb-1">Type</label>
                        <div class="capitalize text-xs font-semibold">{{ $transaction->type }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-[10px] opacity-60 uppercase font-bold block mb-1">Status</label>
                        <div><span class="badge badge-success badge-xs">{{ $transaction->status }}</span></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-[10px] opacity-60 uppercase font-bold block mb-1">Date</label>
                    <div class="text-xs">{{ $transaction->created_at->format('d M Y, H:i') }}</div>
                </div>

                <div class="mb-4">
                    <label class="text-[10px] opacity-60 uppercase font-bold block mb-1">Served By</label>
                    <div class="text-xs">{{ $transaction->user->name }}</div>
                </div>

                <div class="pt-2 border-t border-base-200">
                    <h4 class="text-[10px] opacity-60 uppercase font-bold mb-2">Customer</h4>
                    @if($transaction->customer)
                        <div class="text-sm font-bold">{{ $transaction->customer->name }}</div>
                        <div class="text-xs opacity-70">{{ $transaction->customer->phone }}</div>
                        <div class="text-xs opacity-70">{{ $transaction->customer->email }}</div>
                    @else
                        <div class="italic text-xs opacity-50">Walk-in Customer</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
