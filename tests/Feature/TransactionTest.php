<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Category;
use App\Models\SuperCategory;
use App\Models\ProductLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create(['name' => 'Test Org']);
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        $super = SuperCategory::create(['id' => 1, 'name' => 'Tech']);
        $line = ProductLine::create(['id' => 1, 'name' => 'Phones', 'super_category_id' => $super->id]);
        $category = Category::create(['id' => 1, 'name' => 'Smartphones', 'product_line_id' => $line->id]);

        $this->product = Product::create([
            'name' => 'iPhone 13',
            'category_id' => $category->id,
            'organization_id' => $this->organization->id,
            'sale_price' => 700,
            'quantity' => 10,
        ]);
    }

    public function test_user_can_sell_product_and_stock_decreases()
    {
        $this->withoutExceptionHandling();
        $transactionData = [
            'type' => 'sell',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => 700,
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('transactions.store'), $transactionData);

        if ($response->status() !== 302) {
            dd($response->getContent());
        }
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type' => 'sell',
            'total_amount' => 1400,
            'organization_id' => $this->organization->id,
        ]);

        $this->assertEquals(8, $this->product->fresh()->quantity);
    }

    public function test_user_can_buy_product_and_stock_increases()
    {
        $transactionData = [
            'type' => 'buy',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 500,
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('transactions.store'), $transactionData);

        $this->assertEquals(15, $this->product->fresh()->quantity);
    }
}
