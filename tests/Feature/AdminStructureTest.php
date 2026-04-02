<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProductLine;
use App\Models\SuperCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_structure_page(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $superCategory = SuperCategory::create(['id' => 10, 'name' => 'Phones']);
        $productLine = ProductLine::create(['id' => 11, 'name' => 'Smartphones', 'super_category_id' => $superCategory->id]);
        Category::create(['id' => 12, 'name' => 'Flagships', 'product_line_id' => $productLine->id]);

        $this->actingAs($superAdmin)
            ->get(route('admin.structure'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Structure/Index')
                ->where('structure.0.name', 'Phones')
            );
    }
}
