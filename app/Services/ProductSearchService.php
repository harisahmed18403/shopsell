<?php

namespace App\Services;

use App\Models\CexProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductSearchService
{
    public function tokens(string $search): array
    {
        return array_values(array_filter(
            preg_split('/\s+/', trim($search)) ?: [],
            fn ($token) => $token !== ''
        ));
    }

    public function gradeTokens(array $tokens): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn ($token) => strtoupper(trim($token)), $tokens),
            fn ($token) => in_array($token, ['A', 'B', 'C'], true)
        )));
    }

    public function applyTokenFilters(Builder $query, array $tokens): Builder
    {
        foreach ($tokens as $token) {
            $query->where(function (Builder $builder) use ($token) {
                $builder->where('name', 'like', "%{$token}%")
                    ->orWhere('color', 'like', "%{$token}%")
                    ->orWhere('grade', 'like', "%{$token}%")
                    ->orWhere('description', 'like', "%{$token}%");
            });
        }

        return $query;
    }

    public function applyGradePriorityOrdering(Builder $query, array $gradeTokens): Builder
    {
        if (empty($gradeTokens)) {
            return $query;
        }

        $cases = implode(' ', array_fill(0, count($gradeTokens), 'WHEN upper(grade) = ? THEN 0'));
        $bindings = array_merge($gradeTokens, [1]);

        return $query->orderByRaw("CASE {$cases} ELSE ? END ASC", $bindings);
    }

    public function autocompleteProductIds(array $tokens, int $directLimit = 50, int $fallbackThreshold = 20): array
    {
        $productIds = $this->applyTokenFilters(Product::query()->select('id'), $tokens)
            ->limit($directLimit)
            ->pluck('id')
            ->toArray();

        if (count($productIds) >= $fallbackThreshold) {
            return $productIds;
        }

        $cexQuery = CexProduct::query()->select('product_id');
        foreach ($tokens as $token) {
            $cexQuery->where('name', 'like', "%{$token}%");
        }

        $cexProductIds = $cexQuery->limit($directLimit)->pluck('product_id')->toArray();

        return array_values(array_unique(array_merge($productIds, $cexProductIds)));
    }

    public function scoreProduct(Product $product, array $tokens, array $gradeTokens): int
    {
        $score = 0;
        $productFullName = strtoupper($product->name);

        foreach ($tokens as $token) {
            if (str_contains($productFullName, strtoupper($token))) {
                $score++;
            }
        }

        if (! empty($gradeTokens) && $product->grade && in_array(strtoupper($product->grade), $gradeTokens, true)) {
            $score += 10;
        }

        return $score;
    }

    public function groupedVariants(Product $product): Collection
    {
        return $product->cexProducts
            ->sortByDesc('sale_price')
            ->unique(fn ($variant) => $variant->grade ?? 'N/A')
            ->sortBy(fn ($variant) => match ($variant->grade) {
                'A' => 1,
                'B' => 2,
                'C' => 3,
                default => 4,
            })
            ->values();
    }
}
