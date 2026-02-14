<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->input('q');
        
        if (!$search || strlen($search) < 2) {
            return response()->json([]);
        }

        $tokens = array_filter(explode(' ', $search), fn($t) => strlen(trim($t)) > 0);
        
        // 1. Find matching IDs from products table
        $productQuery = Product::query()->select('id');
        foreach ($tokens as $token) {
            $productQuery->where('name', 'like', "%{$token}%");
        }
        $productIds = $productQuery->limit(50)->pluck('id')->toArray();

        // 2. Find matching IDs from cex_products table if we don't have enough
        if (count($productIds) < 20) {
            $cexQuery = \App\Models\CexProduct::query()->select('product_id');
            foreach ($tokens as $token) {
                $cexQuery->where('name', 'like', "%{$token}%");
            }
            $cexProductIds = $cexQuery->limit(50)->pluck('product_id')->toArray();
            $productIds = array_unique(array_merge($productIds, $cexProductIds));
        }

        if (empty($productIds)) {
            return response()->json([]);
        }

        // 3. Fetch full products with variants
        $products = Product::with('cexProducts')
            ->whereIn('id', $productIds)
            ->get()
            ->map(function ($product) use ($tokens) {
                // Score each variant and find the best one
                $variantsWithScores = $product->cexProducts->map(function($cex) use ($tokens) {
                    $score = 0;
                    $fullName = strtoupper($cex->name . ' ' . $cex->grade);
                    foreach ($tokens as $token) {
                        if (str_contains($fullName, strtoupper($token))) {
                            $score++;
                        }
                    }
                    if ($cex->grade === 'B') $score += 0.1;
                    $cex->match_score = $score;
                    return $cex;
                });

                $bestCex = $variantsWithScores->sortByDesc('match_score')->first();
                
                // Calculate master product score
                $productScore = 0;
                $productFullName = strtoupper($product->name . ' ' . $product->grade);
                foreach ($tokens as $token) {
                    if (str_contains($productFullName, strtoupper($token))) {
                        $productScore++;
                    }
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'grade' => $product->grade,
                    'sale_price' => $product->sale_price,
                    'cash_price' => $product->cash_price,
                    'voucher_price' => $product->voucher_price,
                    'image_url' => $bestCex?->image_url ?? 'https://via.placeholder.com/150',
                    'cex_sale' => $bestCex?->sale_price,
                    'cex_cash' => $bestCex?->cash_price,
                    'cex_voucher' => $bestCex?->voucher_price,
                    'cex_name' => $bestCex?->name,
                    'url' => route('products.show', $product),
                    'overall_score' => max($productScore, $bestCex?->match_score ?? 0)
                ];
            })
            ->sortByDesc('overall_score')
            ->values()
            ->take(10);

        return response()->json($products);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'cexProducts']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        if (request()->wantsJson()) {
            return response()->json($products);
        }
        
        $categories = Category::all();
        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all(); 
        if (request()->wantsJson()) {
            return response()->json($categories);
        }
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sale_price' => 'nullable|numeric',
            'cash_price' => 'nullable|numeric',
            'voucher_price' => 'nullable|numeric',
            'color' => 'nullable|string',
            'grade' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity' => 'integer|min:0',
        ]);

        $product = Product::create($validated);

        if (request()->wantsJson()) {
            return response()->json($product, 201);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('cexProducts');
        if (request()->wantsJson()) {
            return response()->json($product);
        }
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        if (!$product->organization_id && !Auth::user()->isSuperAdmin()) {
            abort(403, 'You cannot edit global products.');
        }

        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if (!$product->organization_id && !Auth::user()->isSuperAdmin()) {
            abort(403, 'You cannot edit global products.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sale_price' => 'nullable|numeric',
            'cash_price' => 'nullable|numeric',
            'voucher_price' => 'nullable|numeric',
            'color' => 'nullable|string',
            'grade' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity' => 'integer|min:0',
        ]);

        $product->update($validated);

        if (request()->wantsJson()) {
            return response()->json($product);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
         if (!$product->organization_id && !Auth::user()->isSuperAdmin()) {
            abort(403, 'You cannot delete global products.');
        }
        
        $product->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Product deleted.']);
        }

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
