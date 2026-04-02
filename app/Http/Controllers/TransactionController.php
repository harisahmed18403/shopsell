<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse|Response
    {
        $query = Transaction::with(['customer', 'user', 'items.product']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('receipt_number', 'like', '%'.$search.'%')
                    ->orWhere('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$search.'%')
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery
                            ->where('description', 'like', '%'.$search.'%')
                            ->orWhere('brand', 'like', '%'.$search.'%')
                            ->orWhere('model', 'like', '%'.$search.'%')
                            ->orWhere('imei_1', 'like', '%'.$search.'%')
                            ->orWhere('imei_2', 'like', '%'.$search.'%');
                    });
            });
        }

        $transactions = $query->latest()->paginate(10)->withQueryString();

        if (request()->wantsJson()) {
            return response()->json($transactions);
        }

        return Inertia::render('Transactions/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'transactions' => $transactions->through(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'receipt_number' => $transaction->receipt_number,
                'created_at' => $transaction->created_at?->toIso8601String(),
                'customer_name' => $transaction->customer?->name ?? $transaction->customer_name ?? 'Guest',
                'total_amount' => (float) $transaction->total_amount,
                'balance_amount' => max(0, (float) $transaction->total_amount - (float) ($transaction->amount_paid ?? $transaction->total_amount)),
                'payment_method' => $transaction->payment_method,
                'items' => $transaction->items->map(function ($item) {
                    return collect([
                        trim(implode(' ', array_filter([$item->brand, $item->model]))),
                        $item->product?->name,
                        $item->description,
                        $item->imei_1,
                    ])->filter()->first();
                })->filter()->values(),
            ])->items(),
            'pagination' => [
                'total' => $transactions->total(),
                'links' => collect($transactions->linkCollection())->map(fn ($link) => [
                    'url' => $link['url'],
                    'label' => strip_tags($link['label']),
                    'active' => $link['active'],
                ])->values(),
            ],
        ]);
    }

    public function create(Request $request): JsonResponse|Response
    {
        $customers = Customer::query()->orderBy('name')->get(['id', 'name', 'email', 'phone']);
        if (request()->wantsJson()) {
            return response()->json(['customers' => $customers]);
        }

        return Inertia::render('Transactions/Create', [
            'customers' => $customers,
            'initialCustomerId' => $request->integer('customer_id') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:buy,sell,repair',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.model' => 'nullable|string|max:255',
            'items.*.storage' => 'nullable|string|max:255',
            'items.*.color' => 'nullable|string|max:255',
            'items.*.imei_1' => 'nullable|string|max:255',
            'items.*.imei_2' => 'nullable|string|max:255',
            'items.*.condition_grade' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $transaction = Transaction::create([
                'receipt_number' => $this->makeReceiptNumber(),
                'type' => $validated['type'],
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'amount_paid' => array_key_exists('amount_paid', $validated) && $validated['amount_paid'] !== null ? $validated['amount_paid'] : $totalAmount,
                'payment_method' => $validated['payment_method'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($validated['items'] as $itemData) {
                TransactionItem::create($this->normalizeItemPayload($transaction->id, $itemData));
            }

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json($transaction->load('items'), 201);
            }

            return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Error processing transaction: '.$e->getMessage());
        }
    }

    public function edit(Transaction $transaction): Response
    {
        $transaction->load(['items.product', 'customer']);
        $customers = Customer::query()->orderBy('name')->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('Transactions/Edit', [
            'customers' => $customers,
            'transaction' => $this->serializeTransaction($transaction),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'type' => 'required|in:buy,sell,repair',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.model' => 'nullable|string|max:255',
            'items.*.storage' => 'nullable|string|max:255',
            'items.*.color' => 'nullable|string|max:255',
            'items.*.imei_1' => 'nullable|string|max:255',
            'items.*.imei_2' => 'nullable|string|max:255',
            'items.*.condition_grade' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Delete Old Items
            $transaction->items()->delete();

            // Update Transaction and Create New Items
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $transaction->update([
                'receipt_number' => $transaction->receipt_number ?: $this->makeReceiptNumber(),
                'type' => $validated['type'],
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'total_amount' => $totalAmount,
                'amount_paid' => array_key_exists('amount_paid', $validated) && $validated['amount_paid'] !== null ? $validated['amount_paid'] : $totalAmount,
                'payment_method' => $validated['payment_method'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                TransactionItem::create($this->normalizeItemPayload($transaction->id, $itemData));
            }

            DB::commit();

            return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error updating transaction: '.$e->getMessage());
        }
    }

    public function show(Transaction $transaction): \Illuminate\Http\JsonResponse|Response
    {
        $transaction->load(['items.product', 'customer', 'user']);
        if (request()->wantsJson()) {
            return response()->json($transaction);
        }

        return Inertia::render('Transactions/Show', [
            'transaction' => $this->serializeTransaction($transaction, true),
        ]);
    }

    public function downloadInvoice(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);

        $data = [
            'transaction' => $transaction,
            'business' => [
                'name' => env('BUSINESS_NAME', 'PhoneWorks Lancaster'),
                'address_lines' => array_filter([
                    env('BUSINESS_ADDRESS_LINE_1', '65 Penny Street'),
                    env('BUSINESS_ADDRESS_LINE_2', 'Lancaster'),
                    env('BUSINESS_ADDRESS_LINE_3', 'LA1 1XF'),
                ]),
                'phone' => env('BUSINESS_PHONE', '01524 935470'),
            ],
        ];

        $pdf = Pdf::loadView('transactions.invoice', $data);

        return $pdf->download('receipt-'.($transaction->receipt_number ?: $transaction->id).'.pdf');
    }

    public function destroy(Transaction $transaction)
    {
        try {
            $transaction->delete();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Transaction deleted.']);
            }

            return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');

        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Error deleting transaction: '.$e->getMessage());
        }
    }

    protected function serializeTransaction(Transaction $transaction, bool $includeMeta = false): array
    {
        return [
            'id' => $transaction->id,
            'receipt_number' => $transaction->receipt_number,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'customer_id' => $transaction->customer_id,
            'customer_name' => $transaction->customer?->name ?? $transaction->customer_name,
            'customer_email' => $transaction->customer?->email ?? $transaction->customer_email,
            'customer_phone' => $transaction->customer?->phone ?? $transaction->customer_phone,
            'total_amount' => (float) $transaction->total_amount,
            'amount_paid' => (float) ($transaction->amount_paid ?? $transaction->total_amount),
            'balance_amount' => max(0, (float) $transaction->total_amount - (float) ($transaction->amount_paid ?? $transaction->total_amount)),
            'payment_method' => $transaction->payment_method,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'user_name' => $includeMeta ? $transaction->user?->name : null,
            'items' => $transaction->items->map(fn (TransactionItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'description' => $item->description,
                'brand' => $item->brand,
                'model' => $item->model,
                'storage' => $item->storage,
                'color' => $item->color,
                'imei_1' => $item->imei_1,
                'imei_2' => $item->imei_2,
                'condition_grade' => $item->condition_grade,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
            ])->values(),
        ];
    }

    protected function normalizeItemPayload(int $transactionId, array $itemData): array
    {
        return [
            'transaction_id' => $transactionId,
            'product_id' => $itemData['product_id'] ?? null,
            'brand' => $itemData['brand'] ?? null,
            'model' => $itemData['model'] ?? null,
            'storage' => $itemData['storage'] ?? null,
            'color' => $itemData['color'] ?? null,
            'imei_1' => $itemData['imei_1'] ?? null,
            'imei_2' => $itemData['imei_2'] ?? null,
            'condition_grade' => $itemData['condition_grade'] ?? null,
            'description' => $itemData['description'] ?? null,
            'quantity' => $itemData['quantity'],
            'price' => $itemData['price'],
        ];
    }

    protected function makeReceiptNumber(): string
    {
        return 'R-'.Str::upper(Str::random(12));
    }
}
