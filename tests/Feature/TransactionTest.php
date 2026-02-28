<?php

namespace Tests\Feature;

use App\Models\User;
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
    protected $product;

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
        $category = Category::create(['id' => 1, 'name' => 'Smartphones', 'product_line_id' => $line->id]);

        $this->product = Product::create([
            'name' => 'iPhone 13',
            'category_id' => $category->id,
            'sale_price' => 700,
        ]);
    }

    public function test_user_can_create_transaction_with_manual_customer_details()
    {
        $transactionData = [
            'type' => 'sell',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '07712345678',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => 700,
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('transactions.store'), $transactionData);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type' => 'sell',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '07712345678',
            'total_amount' => 700,
        ]);
    }

    public function test_user_can_create_transaction_without_customer_details()
    {
        $transactionData = [
            'type' => 'repair',
            'items' => [
                [
                    'description' => 'Screen Replacement',
                    'quantity' => 1,
                    'price' => 100,
                ]
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('transactions.store'), $transactionData);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type' => 'repair',
            'total_amount' => 100,
            'customer_name' => null,
        ]);
    }
}
