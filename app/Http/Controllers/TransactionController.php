<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'user', 'items']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->latest()->paginate(10)->withQueryString();

        if (request()->wantsJson()) {
            return response()->json($transactions);
        }
        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $customers = Customer::all();
        if (request()->wantsJson()) {
            return response()->json(['customers' => $customers]);
        }
        return view('transactions.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:buy,sell,repair',
            'customer_id' => 'nullable|exists:customers,id',
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

                // Update Stock
                if (isset($itemData['product_id'])) {
                    $product = Product::find($itemData['product_id']);
                    if ($product) {
                        if ($validated['type'] === 'sell') {
                            $product->decrement('quantity', $itemData['quantity']);
                        } elseif ($validated['type'] === 'buy') {
                            $product->increment('quantity', $itemData['quantity']);
                        }
                    }
                }
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
            return back()->with('error', 'Error processing transaction: ' . $e->getMessage());
        }
    }

    public function edit(Transaction $transaction)
    {
        $transaction->load('items');
        $customers = Customer::all();
        return view('transactions.edit', compact('transaction', 'customers'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'type' => 'required|in:buy,sell,repair',
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Reverse Old Stock Changes
            foreach ($transaction->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        if ($transaction->type === 'sell') {
                            $product->increment('quantity', $item->quantity);
                        } elseif ($transaction->type === 'buy') {
                            $product->decrement('quantity', $item->quantity);
                        }
                    }
                }
            }

            // 2. Delete Old Items
            $transaction->items()->delete();

            // 3. Update Transaction and Create New Items
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $transaction->update([
                'type' => $validated['type'],
                'customer_id' => $validated['customer_id'] ?? null,
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

                // 4. Apply New Stock Changes
                if (isset($itemData['product_id'])) {
                    $product = Product::find($itemData['product_id']);
                    if ($product) {
                        if ($validated['type'] === 'sell') {
                            $product->decrement('quantity', $itemData['quantity']);
                        } elseif ($validated['type'] === 'buy') {
                            $product->increment('quantity', $itemData['quantity']);
                        }
                    }
                }
            }

            DB::commit();
            
            return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating transaction: ' . $e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user']);
        if (request()->wantsJson()) {
            return response()->json($transaction);
        }
        return view('transactions.show', compact('transaction'));
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
            ]
        ];

        $pdf = Pdf::loadView('transactions.invoice', $data);
        return $pdf->download('invoice-' . $transaction->id . '.pdf');
    }

    public function destroy(Transaction $transaction)
    {
        try {
            DB::beginTransaction();

            // Reverse Stock Changes
            foreach ($transaction->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        if ($transaction->type === 'sell') {
                            $product->increment('quantity', $item->quantity);
                        } elseif ($transaction->type === 'buy') {
                            $product->decrement('quantity', $item->quantity);
                        }
                    }
                }
            }

            $transaction->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json(['message' => 'Transaction deleted and stock reversed.']);
            }

            return redirect()->route('transactions.index')->with('success', 'Transaction deleted and stock updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error deleting transaction: ' . $e->getMessage());
        }
    }

    // Edit/Update is complex for transactions (stock revert?), skipping for brevity unless requested.
    // Usually transactions are immutable or strictly controlled.
}
