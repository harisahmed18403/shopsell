<?php

namespace Tests\Feature;

use App\Models\Customer;
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

        $this->actingAs($user)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Show')
                ->where('customer.name', 'Jane Doe')
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
}
