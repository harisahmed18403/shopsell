<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CexProduct;
use App\Models\Product;
use App\Models\ProductLine;
use App\Models\SuperCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);

        $super = SuperCategory::create(['id' => 1, 'name' => 'Tech']);
        $line = ProductLine::create(['id' => 1, 'name' => 'Phones', 'super_category_id' => $super->id]);
        $this->category = Category::create(['id' => 1, 'name' => 'Smartphones', 'product_line_id' => $line->id]);
    }

    public function test_user_can_view_products_index()
    {
        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Products/Index'));
    }

    public function test_user_can_create_product()
    {
        $productData = [
            'name' => 'iPhone 13',
            'category_id' => $this->category->id,
            'sale_price' => 700,
        ];

        $response = $this->actingAs($this->user)->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'iPhone 13',
        ]);
    }

    public function test_user_can_view_create_product_page()
    {
        $this->actingAs($this->user)
            ->get(route('products.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Products/Create'));
    }

    public function test_user_can_view_product_detail_page()
    {
        $product = Product::create([
            'name' => 'iPhone 15',
            'category_id' => $this->category->id,
            'sale_price' => 999,
        ]);

        $this->actingAs($this->user)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('product.name', 'iPhone 15')
            );
    }

    public function test_user_can_view_edit_product_page()
    {
        $product = Product::create([
            'name' => 'Pixel 9',
            'category_id' => $this->category->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Edit')
                ->where('product.name', 'Pixel 9')
            );
    }

    public function test_product_filtering_works()
    {
        Product::create([
            'name' => 'Apple iPhone',
            'category_id' => $this->category->id,
            'sale_price' => 800,
        ]);

        Product::create([
            'name' => 'Samsung Galaxy',
            'category_id' => $this->category->id,
            'sale_price' => 700,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index', ['search' => 'Apple']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->has('products', 1)
                ->where('products.0.name', 'Apple iPhone')
            );
    }

    public function test_products_index_search_matches_multiple_name_tokens()
    {
        Product::create([
            'name' => 'Apple iPhone 16 128GB Pink',
            'category_id' => $this->category->id,
            'sale_price' => 899,
        ]);

        Product::create([
            'name' => 'Apple iPhone 16 128GB Black',
            'category_id' => $this->category->id,
            'sale_price' => 899,
        ]);

        $this->actingAs($this->user)
            ->get(route('products.index', ['search' => '16 128 pink']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->has('products', 1)
                ->where('products.0.name', 'Apple iPhone 16 128GB Pink')
            );
    }

    public function test_products_index_prioritises_matching_grade_tokens()
    {
        Product::create([
            'name' => 'Apple iPhone 16 128GB Pink',
            'category_id' => $this->category->id,
            'sale_price' => 899,
            'grade' => 'B',
        ]);

        Product::create([
            'name' => 'Apple iPhone 16 128GB Pink',
            'category_id' => $this->category->id,
            'sale_price' => 899,
            'grade' => 'A',
        ]);

        $this->actingAs($this->user)
            ->get(route('products.index', ['search' => '16 128 pink A']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->where('products.0.grade', 'A')
            );
    }

    public function test_products_index_includes_grade_and_grouped_cex_variants()
    {
        $product = Product::create([
            'name' => 'iPhone 15 Pro',
            'category_id' => $this->category->id,
            'sale_price' => 999,
            'grade' => 'B',
        ]);

        CexProduct::create([
            'product_id' => $product->id,
            'cex_id' => 'iphone-15-pro-a-low',
            'name' => 'iPhone 15 Pro Grade A',
            'grade' => 'A',
            'sale_price' => 950,
            'cash_price' => 700,
            'voucher_price' => 780,
        ]);

        CexProduct::create([
            'product_id' => $product->id,
            'cex_id' => 'iphone-15-pro-a-high',
            'name' => 'iPhone 15 Pro Grade A High',
            'grade' => 'A',
            'sale_price' => 975,
            'cash_price' => 720,
            'voucher_price' => 790,
        ]);

        CexProduct::create([
            'product_id' => $product->id,
            'cex_id' => 'iphone-15-pro-c',
            'name' => 'iPhone 15 Pro Grade C',
            'grade' => 'C',
            'sale_price' => 840,
            'cash_price' => 620,
            'voucher_price' => 680,
        ]);

        CexProduct::create([
            'product_id' => $product->id,
            'cex_id' => 'iphone-15-pro-b',
            'name' => 'iPhone 15 Pro Grade B',
            'grade' => 'B',
            'sale_price' => 900,
            'cash_price' => 660,
            'voucher_price' => 730,
        ]);

        $this->actingAs($this->user)
            ->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->where('products.0.grade', 'B')
                ->where('products.0.cex_variants.0.grade', 'A')
                ->where('products.0.cex_variants.0.sale_price', 975)
                ->where('products.0.cex_variants.1.grade', 'B')
                ->where('products.0.cex_variants.2.grade', 'C')
            );
    }

    public function test_product_search_matches_color_tokens_alongside_name_tokens()
    {
        $product = Product::create([
            'name' => 'iPhone 16 128GB',
            'category_id' => $this->category->id,
            'sale_price' => 899,
            'color' => 'Pink',
        ]);

        CexProduct::create([
            'product_id' => $product->id,
            'cex_id' => 'iphone-16-128-pink',
            'name' => 'iPhone 16 128GB Pink',
            'grade' => 'B',
            'sale_price' => 850,
            'cash_price' => 620,
            'voucher_price' => 700,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('products.search', ['q' => '16 128 pink']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $product->id)
            ->assertJsonPath('0.name', 'iPhone 16 128GB');
    }

    public function test_product_search_prioritises_matching_grade_tokens()
    {
        Product::create([
            'name' => 'Apple iPhone 16 128GB Pink',
            'category_id' => $this->category->id,
            'sale_price' => 899,
            'grade' => 'B',
        ]);

        $productA = Product::create([
            'name' => 'Apple iPhone 16 128GB Pink',
            'category_id' => $this->category->id,
            'sale_price' => 899,
            'grade' => 'A',
        ]);

        $this->actingAs($this->user)
            ->getJson(route('products.search', ['q' => '16 128 pink A']))
            ->assertOk()
            ->assertJsonPath('0.id', $productA->id);
    }
}
