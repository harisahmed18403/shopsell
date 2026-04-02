<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_customer_index(): void
    {
        $user = User::factory()->create();
        Customer::create(['name' => 'Jane Doe']);

        $this->actingAs($user)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Index')
                ->has('customers', 1)
            );
    }

    public function test_user_can_view_customer_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('customers.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Customers/Create'));
    }

    public function test_user_can_view_customer_show_page(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Jane Doe']);
        Transaction::create([
            'receipt_number' => 'R-CUSTOMER-1',
            'type' => 'sell',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_amount' => 200,
            'amount_paid' => 150,
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Show')
                ->where('customer.name', 'Jane Doe')
                ->where('customer.summary.transaction_count', 1)
                ->where('customer.summary.outstanding_balance', 50)
                ->where('customer.transactions.0.receipt_number', 'R-CUSTOMER-1')
            );
    }

    public function test_user_can_view_customer_edit_page(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Jane Doe']);

        $this->actingAs($user)
            ->get(route('customers.edit', $customer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Edit')
                ->where('customer.name', 'Jane Doe')
            );
    }

    public function test_user_can_filter_customer_index(): void
    {
        $user = User::factory()->create();
        Customer::create(['name' => 'Jane Doe']);
        Customer::create(['name' => 'John Smith']);

        $this->actingAs($user)
            ->get(route('customers.index', ['search' => 'Jane']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Index')
                ->has('customers', 1)
                ->where('customers.0.name', 'Jane Doe')
            );
    }

    public function test_customer_index_includes_lifetime_value(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Jane Doe']);
        Transaction::create([
            'receipt_number' => 'R-CUSTOMER-2',
            'type' => 'sell',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_amount' => 275,
            'amount_paid' => 275,
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Index')
                ->where('customers.0.lifetime_value', 275)
            );
    }

    public function test_user_can_delete_customer(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Jane Doe']);

        $this->actingAs($user)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
