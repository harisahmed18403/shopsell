<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\SuperCategory;
use App\Models\ProductLine;
use App\Models\CexProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class CexRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);

        $super = SuperCategory::create(['id' => 4, 'name' => 'Apple Tech']);
        $line = ProductLine::create(['id' => 83, 'name' => 'iPhones', 'super_category_id' => $super->id]);
        $category = Category::create(['id' => 1225, 'name' => 'iPhone 11', 'product_line_id' => $line->id]);

        Product::create([
            'name' => 'iPhone 11 64GB Black',
            'category_id' => $category->id,
            'sale_price' => 200,
        ]);
    }

    public function test_cex_refresh_command_syncs_data()
    {
        Http::fake([
            'https://search.webuy.io/1/indexes/*/queries' => Http::response([
                'results' => [[
                    'hits' => [[
                        'objectID' => 'cex-iphone-11-64-black-b',
                        'boxName' => 'iPhone 11 64GB Black, Unlocked',
                        'categoryId' => 1225,
                        'Grade' => ['B'],
                        'cashPriceCalculated' => 120.00,
                        'exchangePriceCalculated' => 150.00,
                        'sellPrice' => 200.00,
                        'imageUrls' => [
                            'medium' => 'https://example.test/images/iphone-11.jpg',
                        ],
                    ]],
                    'nbPages' => 1,
                ]],
            ], 200),
        ]);

        Artisan::call('cex:refresh', [
            '--category-id' => 1225,
            '--limit' => 5
        ]);

        $cexProducts = CexProduct::all();

        $this->assertCount(1, $cexProducts);

        $cexProduct = $cexProducts->first();
        $this->assertSame('cex-iphone-11-64-black-b', $cexProduct->cex_id);
        $this->assertSame('iPhone 11 64GB Black, Unlocked', $cexProduct->name);
        $this->assertNotNull($cexProduct->cash_price);
        $this->assertNotNull($cexProduct->sale_price);
        $this->assertNotNull($cexProduct->voucher_price);
        $this->assertNotNull($cexProduct->product_id);
        $this->assertSame('iPhone 11 64GB Black', $cexProduct->product->name);

        Http::assertSentCount(1);
    }
}
