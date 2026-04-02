<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse|Response
    {
        $query = Transaction::with(['customer', 'user', 'items.product']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(10)->withQueryString();

        if (request()->wantsJson()) {
            return response()->json($transactions);
        }

        return Inertia::render('Transactions/Index', [
            'filters' => [
                'type' => $request->string('type')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'transactions' => $transactions->through(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at?->toIso8601String(),
                'customer_name' => $transaction->customer?->name ?? $transaction->customer_name ?? 'Guest',
                'total_amount' => (float) $transaction->total_amount,
                'items' => $transaction->items->map(fn ($item) => $item->product?->name ?? $item->description)->filter()->values(),
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

    public function create(): \Illuminate\Http\JsonResponse|Response
    {
        $customers = Customer::query()->orderBy('name')->get(['id', 'name', 'email', 'phone']);
        if (request()->wantsJson()) {
            return response()->json(['customers' => $customers]);
        }

        return Inertia::render('Transactions/Create', [
            'customers' => $customers,
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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
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
                'type' => $validated['type'],
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'status' => 'completed',
            ]);

            foreach ($validated['items'] as $itemData) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                ]);
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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
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
                'type' => $validated['type'],
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'total_amount' => $totalAmount,
            ]);

            foreach ($validated['items'] as $itemData) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                ]);
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
                'name' => env('BUSINESS_NAME', 'ShopSell'),
                'address' => env('BUSINESS_ADDRESS', '123 Street, City, Postcode'),
                'phone' => env('BUSINESS_PHONE', '0123456789'),
                'email' => env('BUSINESS_EMAIL', 'contact@shopsell.com'),
            ],
        ];

        $pdf = Pdf::loadView('transactions.invoice', $data);

        return $pdf->download('invoice-'.$transaction->id.'.pdf');
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
            'type' => $transaction->type,
            'status' => $transaction->status,
            'customer_id' => $transaction->customer_id,
            'customer_name' => $transaction->customer?->name ?? $transaction->customer_name,
            'customer_email' => $transaction->customer?->email ?? $transaction->customer_email,
            'customer_phone' => $transaction->customer?->phone ?? $transaction->customer_phone,
            'total_amount' => (float) $transaction->total_amount,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'user_name' => $includeMeta ? $transaction->user?->name : null,
            'items' => $transaction->items->map(fn (TransactionItem $item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
            ])->values(),
        ];
    }
}
