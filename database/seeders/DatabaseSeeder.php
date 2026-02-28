<?php

namespace Database\Seeders;

use App\Models\User;
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
        // 1. Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@shopsell.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        // 2. Create Regular Admin
        User::create([
            'name' => 'Shop Manager',
            'email' => 'manager@shopsell.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 3. Create Product Structure
        $super = SuperCategory::create(['id' => 1, 'name' => 'Apple Tech']);
        $line = ProductLine::create(['id' => 1, 'name' => 'iPhones', 'super_category_id' => $super->id]);
        $cat = Category::create(['id' => 1, 'name' => 'iPhone 11', 'product_line_id' => $line->id]);

        $super2 = SuperCategory::create(['id' => 2, 'name' => 'Samsung Tech']);
        $line2 = ProductLine::create(['id' => 2, 'name' => 'Galaxy S Series', 'super_category_id' => $super2->id]);
        $cat2 = Category::create(['id' => 2, 'name' => 'Galaxy S20', 'product_line_id' => $line2->id]);

        // 4. Create Products
        Product::create([
            'name' => 'iPhone 11 256GB Black',
            'category_id' => $cat->id,
            'sale_price' => 400.00,
            'cash_price' => 350.00,
            'voucher_price' => 380.00,
            'color' => 'Black',
            'grade' => 'A',
            'description' => 'Mint condition',
        ]);

        Product::create([
            'name' => 'Galaxy S20 128GB Grey',
            'category_id' => $cat2->id,
            'sale_price' => 300.00,
            'cash_price' => 250.00,
            'voucher_price' => 280.00,
            'color' => 'Grey',
            'grade' => 'B',
            'description' => 'Few scratches',
        ]);
    }
}
