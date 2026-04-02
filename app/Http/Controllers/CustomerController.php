<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $customers = Customer::query()
            ->withCount('transactions')
            ->withSum('transactions as lifetime_value', 'total_amount')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
            'customers' => $customers->through(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'transactions_count' => $customer->transactions_count,
                'lifetime_value' => (float) ($customer->lifetime_value ?? 0),
                'created_at' => $customer->created_at?->toIso8601String(),
            ])->items(),
            'pagination' => [
                'total' => $customers->total(),
                'links' => collect($customers->linkCollection())->map(fn ($link) => [
                    'url' => $link['url'],
                    'label' => strip_tags($link['label']),
                    'active' => $link['active'],
                ])->values(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): Response
    {
        $customer->load('transactions.items.product');
        $transactions = $customer->transactions->sortByDesc('created_at')->values();
        $lifetimeValue = (float) $transactions->sum('total_amount');
        $outstandingBalance = (float) $transactions->sum(fn ($transaction) => max(0, (float) $transaction->total_amount - (float) ($transaction->amount_paid ?? $transaction->total_amount)));

        return Inertia::render('Customers/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'summary' => [
                    'transaction_count' => $transactions->count(),
                    'lifetime_value' => $lifetimeValue,
                    'outstanding_balance' => $outstandingBalance,
                    'last_transaction_at' => $transactions->first()?->created_at?->toIso8601String(),
                ],
                'transactions' => $transactions->map(fn ($transaction) => [
                    'id' => $transaction->id,
                    'receipt_number' => $transaction->receipt_number,
                    'type' => $transaction->type,
                    'status' => $transaction->status,
                    'total_amount' => (float) $transaction->total_amount,
                    'balance_amount' => max(0, (float) $transaction->total_amount - (float) ($transaction->amount_paid ?? $transaction->total_amount)),
                    'created_at' => $transaction->created_at?->toIso8601String(),
                    'items' => $transaction->items->map(function ($item) {
                        return trim(implode(' ', array_filter([$item->brand, $item->model])))
                            ?: $item->product?->name
                            ?: $item->description
                            ?: 'Custom item';
                    })->values(),
                ])->values(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
