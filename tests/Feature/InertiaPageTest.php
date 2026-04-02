<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_through_inertia(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('app.name', config('app.name'))
                ->where('auth.user', null)
            );
    }

    public function test_dashboard_page_renders_expected_props(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '01234 567890',
        ]);

        Transaction::create([
            'type' => 'sell',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_amount' => 123.45,
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('auth.user.email', $user->email)
                ->where('routing.current', 'dashboard')
                ->has('recentTransactions', 1)
                ->where('recentTransactions.0.customer_name', 'Jane Doe')
            );
    }

    public function test_reports_page_renders_expected_props(): void
    {
        $user = User::factory()->create();

        Transaction::create([
            'type' => 'sell',
            'user_id' => $user->id,
            'total_amount' => 200,
            'status' => 'completed',
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);

        Transaction::create([
            'type' => 'buy',
            'user_id' => $user->id,
            'total_amount' => 150,
            'status' => 'completed',
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);

        $this->actingAs($user)
            ->get(route('reports'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports')
                ->where('routing.current', 'reports')
                ->has('monthlyData')
                ->has('buyVsSell')
                ->has('profitData')
            );
    }
}
