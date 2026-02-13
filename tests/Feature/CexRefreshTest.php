<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Category;
use App\Models\SuperCategory;
use App\Models\ProductLine;
use App\Models\CexProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class CexRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::create(['name' => 'Test Org']);
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        $super = SuperCategory::create(['id' => 4, 'name' => 'Apple Tech']);
        $line = ProductLine::create(['id' => 83, 'name' => 'iPhones', 'super_category_id' => $super->id]);
        $category = Category::create(['id' => 1225, 'name' => 'iPhone 11', 'product_line_id' => $line->id]);

        Product::create([
            'name' => 'iPhone 11 64GB Black',
            'category_id' => $category->id,
            'organization_id' => $this->organization->id,
            'sale_price' => 200,
            'quantity' => 5,
        ]);
    }

    public function test_cex_refresh_command_syncs_data()
    {
        // This test actually calls the CeX API. 
        // We use category 1225 (iPhone 11) and limit to 5.
        Artisan::call('cex:refresh', [
            '--category-id' => 1225,
            '--limit' => 5
        ]);

        $cexProducts = CexProduct::all();
        
        // It should have synced at least one product if the API is up and our matching works
        $this->assertGreaterThan(0, $cexProducts->count(), "No CeX products were synced. Check if API is blocked or naming match failed.");
        
        $cexProduct = $cexProducts->first();
        $this->assertNotNull($cexProduct->cash_price);
        $this->assertNotNull($cexProduct->sale_price);
        $this->assertNotNull($cexProduct->voucher_price);
        $this->assertNotNull($cexProduct->product_id);
    }
}
