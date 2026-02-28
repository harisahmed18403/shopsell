<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuperCategory;
use App\Models\ProductLine;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response->assertStatus(200);
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

        $response->assertSee('Apple iPhone');
        $response->assertDontSee('Samsung Galaxy');
    }
}
