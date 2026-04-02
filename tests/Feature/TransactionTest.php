<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductLine;
use App\Models\SuperCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $product;

    protected $customer;

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

        $this->customer = Customer::create([
            'name' => 'Existing Customer',
            'email' => 'existing@example.com',
            'phone' => '07000000000',
        ]);
    }

    public function test_user_can_view_transaction_index_page()
    {
        $this->actingAs($this->user)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Transactions/Index'));
    }

    public function test_user_can_view_transaction_create_page()
    {
        $this->actingAs($this->user)
            ->get(route('transactions.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Transactions/Create')
                ->has('customers')
            );
    }

    public function test_transaction_create_page_can_prefill_customer()
    {
        $this->actingAs($this->user)
            ->get(route('transactions.create', ['customer_id' => $this->customer->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Transactions/Create')
                ->where('initialCustomerId', $this->customer->id)
            );
    }

    public function test_user_can_create_transaction_with_manual_customer_details()
    {
        $transactionData = [
            'type' => 'sell',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '07712345678',
            'payment_method' => 'Cash',
            'amount_paid' => 850,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'brand' => 'Apple',
                    'model' => 'iPhone 16 Pro Max',
                    'storage' => '256 GB',
                    'color' => 'Black Titanium',
                    'imei_1' => '354760243811073',
                    'imei_2' => '354760243542140',
                    'condition_grade' => 'A',
                    'quantity' => 1,
                    'price' => 850,
                ],
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
            'payment_method' => 'Cash',
            'amount_paid' => 850,
            'total_amount' => 850,
        ]);
        $this->assertDatabaseHas('transaction_items', [
            'brand' => 'Apple',
            'model' => 'iPhone 16 Pro Max',
            'storage' => '256 GB',
            'color' => 'Black Titanium',
            'imei_1' => '354760243811073',
            'imei_2' => '354760243542140',
            'condition_grade' => 'A',
            'price' => 850,
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
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('transactions.store'), $transactionData);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type' => 'repair',
            'total_amount' => 100,
            'amount_paid' => 100,
            'customer_name' => null,
        ]);
    }

    public function test_user_can_view_transaction_show_and_edit_pages()
    {
        $this->actingAs($this->user)->post(route('transactions.store'), [
            'type' => 'sell',
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => 700,
                ],
            ],
        ]);

        $transaction = \App\Models\Transaction::firstOrFail();

        $this->actingAs($this->user)
            ->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Transactions/Show')
                ->where('transaction.id', $transaction->id)
                ->where('transaction.receipt_number', $transaction->receipt_number)
            );

        $this->actingAs($this->user)
            ->get(route('transactions.edit', $transaction))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Transactions/Edit')
                ->where('transaction.id', $transaction->id)
            );
    }

    public function test_invoice_download_includes_receipt_headers()
    {
        $this->actingAs($this->user)->post(route('transactions.store'), [
            'type' => 'sell',
            'customer_name' => 'Rana Ahsan Ali',
            'payment_method' => 'Cash',
            'amount_paid' => 850,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'brand' => 'Apple',
                    'model' => 'iPhone 16 Pro Max',
                    'storage' => '256 GB',
                    'color' => 'Black Titanium',
                    'imei_1' => '354760243811073',
                    'imei_2' => '354760243542140',
                    'condition_grade' => 'A',
                    'quantity' => 1,
                    'price' => 850,
                ],
            ],
        ]);

        $transaction = \App\Models\Transaction::with('items')->firstOrFail();

        $response = $this->actingAs($this->user)->get(route('transactions.invoice', $transaction));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
    }

    public function test_user_can_delete_transaction()
    {
        $this->actingAs($this->user)->post(route('transactions.store'), [
            'type' => 'sell',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'price' => 700,
                ],
            ],
        ]);

        $transaction = \App\Models\Transaction::firstOrFail();

        $this->actingAs($this->user)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
