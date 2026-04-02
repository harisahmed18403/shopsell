<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductLine;
use App\Models\SuperCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InventoryTest extends TestCase
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
        ]);
    }

    public function test_user_can_view_inventory_index()
    {
        $response = $this->actingAs($this->user)->get(route('inventory.index'));
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Index'));
    }

    public function test_user_can_view_inventory_create_page()
    {
        $this->actingAs($this->user)
            ->get(route('inventory.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Create'));
    }

    public function test_user_can_view_inventory_edit_page()
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'imei' => 'EDIT-ME',
            'condition' => 'Good',
            'purchase_price' => 400,
            'sale_price' => 650,
            'status' => 'available',
        ]);

        $this->actingAs($this->user)
            ->get(route('inventory.edit', $item))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Edit')
                ->where('item.id', $item->id)
                ->where('item.imei', 'EDIT-ME')
            );
    }

    public function test_user_can_add_item_to_inventory()
    {
        $itemData = [
            'product_id' => $this->product->id,
            'imei' => 'IMEI123456',
            'condition' => 'Mint',
            'purchase_price' => 500,
            'sale_price' => 700,
        ];

        $response = $this->actingAs($this->user)->post(route('inventory.store'), $itemData);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseHas('inventory_items', [
            'product_id' => $this->product->id,
            'imei' => 'IMEI123456',
        ]);
    }

    public function test_user_can_remove_item_from_inventory()
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'imei' => 'TO_DELETE',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->user)->delete(route('inventory.destroy', $item));

        $response->assertRedirect();
        $this->assertDatabaseMissing('inventory_items', ['id' => $item->id]);
    }

    public function test_user_can_update_inventory_item()
    {
        $item = InventoryItem::create([
            'product_id' => $this->product->id,
            'imei' => 'OLD-IMEI',
            'condition' => 'Fair',
            'purchase_price' => 300,
            'sale_price' => 500,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->user)->put(route('inventory.update', $item), [
            'product_id' => $this->product->id,
            'imei' => 'NEW-IMEI',
            'condition' => 'Excellent',
            'purchase_price' => 350,
            'sale_price' => 575,
            'status' => 'reserved',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'imei' => 'NEW-IMEI',
            'condition' => 'Excellent',
            'purchase_price' => 350,
            'sale_price' => 575,
            'status' => 'reserved',
        ]);
    }
}
