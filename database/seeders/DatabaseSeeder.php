<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\SuperCategory;
use App\Models\ProductLine;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin (No Org)
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@saas.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'organization_id' => null,
        ]);

        // 2. Create Organization
        $org = Organization::create([
            'name' => 'Phone Shop A',
            'details' => 'Best phones in town',
        ]);

        // 3. Create Org Admin
        User::factory()->create([
            'name' => 'Shop Owner',
            'email' => 'owner@shopa.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'organization_id' => $org->id,
        ]);

        // 4. Create Product Structure (Global for now, org_id = null)
        $super = SuperCategory::create(['name' => 'Apple Tech']);
        $line = ProductLine::create(['name' => 'iPhones', 'super_category_id' => $super->id]);
        $cat = Category::create(['name' => 'iPhone 11', 'product_line_id' => $line->id]);

        $super2 = SuperCategory::create(['name' => 'Samsung Tech']);
        $line2 = ProductLine::create(['name' => 'Galaxy S Series', 'super_category_id' => $super2->id]);
        $cat2 = Category::create(['name' => 'Galaxy S20', 'product_line_id' => $line2->id]);

        // 5. Create a Product for Org A
        Product::create([
            'name' => 'iPhone 11 256GB Black',
            'category_id' => $cat->id,
            'organization_id' => $org->id, // Explicitly set, or login as user to use trait. Here explicit.
            'sale_price' => 400.00,
            'cash_price' => 350.00,
            'voucher_price' => 380.00,
            'color' => 'Black',
            'grade' => 'A',
            'description' => 'Mint condition',
            'quantity' => 5,
        ]);
    }
}
