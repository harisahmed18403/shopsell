<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory.
     */
    public function index(Request $request): Response
    {
        $query = InventoryItem::with(['product.category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('imei', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $items = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Inventory/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
            'items' => $items->through(fn (InventoryItem $item) => [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? 'Unknown Product',
                'category' => $item->product?->category?->name,
                'imei' => $item->imei,
                'condition' => $item->condition,
                'purchase_price' => (float) ($item->purchase_price ?? 0),
                'sale_price' => (float) ($item->sale_price ?? 0),
                'status' => $item->status,
                'created_at' => $item->created_at?->toIso8601String(),
            ])->items(),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'links' => collect($items->linkCollection())->map(fn ($link) => [
                    'url' => $link['url'],
                    'label' => strip_tags($link['label']),
                    'active' => $link['active'],
                ])->values(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create()
    {
        return Inertia::render('Inventory/Create');
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'imei' => 'nullable|string|max:255',
            'condition' => 'nullable|string|max:255',
            'purchase_price' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
        ]);

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Item added to inventory.');
    }

    /**
     * Remove the specified item from inventory.
     */
    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();

        return back()->with('success', 'Item removed from inventory.');
    }
}
