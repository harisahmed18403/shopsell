<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductSearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function search(Request $request, ProductSearchService $productSearchService)
    {
        $search = $request->input('q');

        if (! $search || strlen($search) < 2) {
            return response()->json([]);
        }

        $tokens = $productSearchService->tokens($search);
        $gradeTokens = $productSearchService->gradeTokens($tokens);
        $productIds = $productSearchService->autocompleteProductIds($tokens);

        if (empty($productIds)) {
            return response()->json([]);
        }

        // 3. Fetch full products with variants
        $products = Product::with('cexProducts')
            ->whereIn('id', $productIds)
            ->get()
            ->map(function ($product) use ($productSearchService, $tokens, $gradeTokens) {
                $variants = $productSearchService->groupedVariants($product)
                    ->map(function ($cex) {
                        return [
                            'grade' => $cex->grade ?? 'N/A',
                            'sale' => $cex->sale_price,
                            'cash' => $cex->cash_price,
                            'voucher' => $cex->voucher_price,
                        ];
                    });

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_url' => $product->cexProducts->first()?->image_url ?? 'https://via.placeholder.com/150',
                    'variants' => $variants ?? [],
                    'url' => route('products.show', $product),
                    'score' => $productSearchService->scoreProduct($product, $tokens, $gradeTokens),
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
    public function index(Request $request, ProductSearchService $productSearchService): \Illuminate\Http\JsonResponse|Response
    {
        $query = Product::with(['category', 'cexProducts']);

        if ($request->filled('search')) {
            $tokens = $productSearchService->tokens($request->string('search')->toString());
            $gradeTokens = $productSearchService->gradeTokens($tokens);
            $productSearchService->applyTokenFilters($query, $tokens);
            $productSearchService->applyGradePriorityOrdering($query, $gradeTokens);
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
            'products' => $products->through(function (Product $product) use ($productSearchService) {
                $variants = $productSearchService->groupedVariants($product)
                    ->map(fn ($variant) => [
                        'grade' => $variant->grade ?? 'N/A',
                        'sale_price' => (float) $variant->sale_price,
                        'cash_price' => (float) $variant->cash_price,
                        'voucher_price' => (float) $variant->voucher_price,
                    ]);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'grade' => $product->grade,
                    'color' => $product->color,
                    'sale_price' => (float) ($product->sale_price ?? 0),
                    'image_url' => $product->cexProducts->first()?->image_url,
                    'cex_variants' => $variants,
                ];
            })->items(),
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
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        if (request()->wantsJson()) {
            return response()->json($categories);
        }

        return Inertia::render('Products/Create', [
            'categories' => $categories,
        ]);
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
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Edit', [
            'categories' => $categories,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'sale_price' => $product->sale_price,
                'cash_price' => $product->cash_price,
                'voucher_price' => $product->voucher_price,
                'color' => $product->color,
                'grade' => $product->grade,
                'description' => $product->description,
            ],
        ]);
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
