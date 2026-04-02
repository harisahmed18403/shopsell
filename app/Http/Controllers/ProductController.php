<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->input('q');

        if (! $search || strlen($search) < 2) {
            return response()->json([]);
        }

        $tokens = array_filter(explode(' ', $search), fn ($t) => strlen(trim($t)) > 0);

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
                // Get variants, unique by grade, sorted A -> B -> C
                $variants = $product->cexProducts->sortByDesc('sale_price') // Take most expensive if duplicate grade
                    ->unique('grade')
                    ->sortBy(function ($cex) {
                        return match ($cex->grade) {
                            'A' => 1,
                            'B' => 2,
                            'C' => 3,
                            default => 4
                        };
                    })->values()->map(function ($cex) {
                        return [
                            'grade' => $cex->grade ?? 'N/A',
                            'sale' => $cex->sale_price,
                            'cash' => $cex->cash_price,
                            'voucher' => $cex->voucher_price,
                        ];
                    });

                // Calculate relevance score for sorting
                $productScore = 0;
                $productFullName = strtoupper($product->name);
                foreach ($tokens as $token) {
                    if (str_contains($productFullName, strtoupper($token))) {
                        $productScore++;
                    }
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_url' => $product->cexProducts->first()?->image_url ?? 'https://via.placeholder.com/150',
                    'variants' => $variants ?? [],
                    'url' => route('products.show', $product),
                    'score' => $productScore,
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take(10);

        return response()->json($products);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse|Response
    {
        $query = Product::with(['category', 'cexProducts']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        if (request()->wantsJson()) {
            return response()->json($products);
        }

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Index', [
            'filters' => [
                'search' => $request->string('search')->toString(),
                'category_id' => $request->string('category_id')->toString(),
            ],
            'categories' => $categories,
            'products' => $products->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'color' => $product->color,
                'sale_price' => (float) ($product->sale_price ?? 0),
                'image_url' => $product->cexProducts->first()?->image_url,
                'cex_best_variant' => optional($product->cexProducts->where('grade', 'B')->first() ?? $product->cexProducts->first(), function ($variant) {
                    return [
                        'sale_price' => (float) $variant->sale_price,
                        'cash_price' => (float) $variant->cash_price,
                    ];
                }),
            ])->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'links' => collect($products->linkCollection())->map(fn ($link) => [
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
    public function show(Product $product): \Illuminate\Http\JsonResponse|Response
    {
        $product->load(['category', 'cexProducts']);
        if (request()->wantsJson()) {
            return response()->json($product);
        }

        return Inertia::render('Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'grade' => $product->grade,
                'color' => $product->color,
                'description' => $product->description,
                'sale_price' => (float) ($product->sale_price ?? 0),
                'cash_price' => (float) ($product->cash_price ?? 0),
                'voucher_price' => (float) ($product->voucher_price ?? 0),
                'image_url' => $product->cexProducts->first()?->image_url,
                'last_synced_at' => $product->cexProducts->first()?->last_synced_at?->toIso8601String(),
                'cex_products' => $product->cexProducts->map(fn ($cexProduct) => [
                    'id' => $cexProduct->id,
                    'name' => $cexProduct->name,
                    'grade' => $cexProduct->grade,
                    'sale_price' => (float) $cexProduct->sale_price,
                    'cash_price' => (float) $cexProduct->cash_price,
                    'voucher_price' => (float) $cexProduct->voucher_price,
                ])->values(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
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
        $product->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Product deleted.']);
        }

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
