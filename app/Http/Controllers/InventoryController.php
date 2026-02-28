<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory.
     */
    public function index(Request $request)
    {
        $query = InventoryItem::with(['product.category']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('imei', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->latest()->paginate(20)->withQueryString();

        return view('inventory.index', compact('items'));
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create()
    {
        return view('inventory.create');
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
