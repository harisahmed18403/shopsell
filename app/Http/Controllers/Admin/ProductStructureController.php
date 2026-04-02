<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuperCategory;
use Inertia\Inertia;
use Inertia\Response;

class ProductStructureController extends Controller
{
    public function index(): Response
    {
        $structure = SuperCategory::query()
            ->with(['productLines.categories.products'])
            ->orderBy('name')
            ->get()
            ->map(fn (SuperCategory $superCategory) => [
                'id' => $superCategory->id,
                'name' => $superCategory->name,
                'product_lines' => $superCategory->productLines->sortBy('name')->map(fn ($line) => [
                    'id' => $line->id,
                    'name' => $line->name,
                    'categories' => $line->categories->sortBy('name')->map(fn ($category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'products_count' => $category->products->count(),
                    ])->values(),
                ])->values(),
            ])->values();

        return Inertia::render('Admin/Structure/Index', [
            'structure' => $structure,
        ]);
    }
}
