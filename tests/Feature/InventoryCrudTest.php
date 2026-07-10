<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminDashboardController;
use App\Models\Material;
use App\Models\MaterialUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class InventoryCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('material_usages');
        Schema::dropIfExists('material_deliveries');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('projects');

        Schema::create('materials', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit');
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('minimum_stock_level', 12, 2)->default(0);
            $table->string('supplier')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function ($table) {
            $table->id('project_id');
            $table->string('project_name');
            $table->timestamps();
        });

        Schema::create('material_usages', function ($table) {
            $table->id();
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->decimal('quantity_used', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->date('usage_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function test_inventory_search_filters_results(): void
    {
        $controller = new AdminDashboardController();

        Material::create([
            'name' => 'Portland Cement',
            'category' => 'Masonry',
            'unit' => 'Bag',
            'current_stock' => 20,
            'minimum_stock_level' => 5,
            'supplier' => 'Build Supply',
            'description' => 'Structural material',
        ]);

        Material::create([
            'name' => 'Steel Rebar',
            'category' => 'Structural',
            'unit' => 'Ton',
            'current_stock' => 10,
            'minimum_stock_level' => 3,
            'supplier' => 'Steel Co',
            'description' => 'Structural steel',
        ]);

        $response = $controller->inventory(Request::create('/admin/inventory', 'GET', ['search' => 'cement']));
        $data = $response->getData();

        $this->assertCount(1, $data['materials']);
        $this->assertSame('Portland Cement', $data['materials']->first()->name);
    }

    public function test_inventory_search_filters_material_usage_logs(): void
    {
        $controller = new AdminDashboardController();

        $material = Material::create([
            'name' => 'Portland Cement',
            'category' => 'Masonry',
            'unit' => 'Bag',
            'current_stock' => 20,
            'minimum_stock_level' => 5,
            'supplier' => 'Build Supply',
            'description' => 'Structural material',
        ]);

        MaterialUsage::create([
            'material_id' => $material->id,
            'quantity_used' => 3,
            'unit' => 'Bag',
            'usage_date' => now()->toDateString(),
            'remarks' => 'Used for foundation work',
        ]);

        MaterialUsage::create([
            'material_id' => $material->id,
            'quantity_used' => 2,
            'unit' => 'Bag',
            'usage_date' => now()->toDateString(),
            'remarks' => 'Used for roof work',
        ]);

        $response = $controller->inventory(Request::create('/admin/inventory', 'GET', ['search' => 'foundation']));
        $data = $response->getData();

        $this->assertCount(1, $data['usageLogs']);
        $this->assertStringContainsString('foundation', strtolower($data['usageLogs']->first()->remarks));
    }

    public function test_inventory_usage_logs_can_be_filtered_by_category_and_status(): void
    {
        $controller = new AdminDashboardController();

        $masonryMaterial = Material::create([
            'name' => 'Portland Cement',
            'category' => 'Masonry',
            'unit' => 'Bag',
            'current_stock' => 20,
            'minimum_stock_level' => 5,
            'supplier' => 'Build Supply',
            'description' => 'Structural material',
        ]);

        $structuralMaterial = Material::create([
            'name' => 'Steel Rebar',
            'category' => 'Structural',
            'unit' => 'Ton',
            'current_stock' => 10,
            'minimum_stock_level' => 3,
            'supplier' => 'Steel Co',
            'description' => 'Structural steel',
        ]);

        MaterialUsage::create([
            'material_id' => $masonryMaterial->id,
            'quantity_used' => 3,
            'unit' => 'Bag',
            'usage_date' => now()->toDateString(),
            'remarks' => 'Used for foundation work',
        ]);

        MaterialUsage::create([
            'material_id' => $structuralMaterial->id,
            'quantity_used' => 2,
            'unit' => 'Ton',
            'usage_date' => now()->toDateString(),
            'remarks' => null,
        ]);

        $response = $controller->inventory(Request::create('/admin/inventory', 'GET', [
            'view' => 'usage',
            'usage_category' => 'Masonry',
            'usage_status' => 'with_remarks',
        ]));
        $data = $response->getData();

        $this->assertCount(1, $data['usageLogs']);
        $this->assertSame('Portland Cement', $data['usageLogs']->first()->material->name);
    }

    public function test_inventory_crud_actions_work(): void
    {
        $controller = new AdminDashboardController();

        $storeRequest = Request::create('/admin/inventory/materials', 'POST', [
            'name' => 'Portland Cement',
            'category' => 'Masonry',
            'unit' => 'Bag',
            'current_stock' => 20,
            'minimum_stock_level' => 5,
            'supplier' => 'Build Supply',
            'description' => 'Main structural material',
        ]);

        $storeResponse = $controller->storeMaterial($storeRequest);
        $this->assertTrue($storeResponse->isRedirect());
        $this->assertEquals('Material added successfully.', session('success'));

        $material = Material::query()->where('name', 'Portland Cement')->firstOrFail();

        $updateRequest = Request::create('/admin/inventory/materials/' . $material->id, 'PUT', [
            'name' => 'Portland Cement Premium',
            'category' => 'Masonry',
            'unit' => 'Bag',
            'minimum_stock_level' => 8,
            'supplier' => 'Build Supply Co',
            'description' => 'Updated description',
        ]);

        $updateResponse = $controller->updateMaterial($updateRequest, $material);
        $this->assertTrue($updateResponse->isRedirect());
        $this->assertEquals('Material updated successfully.', session('success'));

        $material->refresh();
        $this->assertEquals('Portland Cement Premium', $material->name);
        $this->assertEquals(8, (float) $material->minimum_stock_level);

        $receiveRequest = Request::create('/admin/inventory/materials/' . $material->id . '/receive', 'POST', [
            'quantity_received' => 15,
            'received_date' => now()->toDateString(),
            'supplier' => 'Build Supply Co',
        ]);

        $receiveResponse = $controller->receiveStock($receiveRequest, $material);
        $this->assertTrue($receiveResponse->isRedirect());
        $this->assertEquals('Stock received successfully.', session('success'));

        $material->refresh();
        $this->assertEquals(35, (float) $material->current_stock);

        $deleteResponse = $controller->destroyMaterial($material);
        $this->assertTrue($deleteResponse->isRedirect());
        $this->assertEquals('Material deleted successfully.', session('success'));
        $this->assertNull(Material::query()->find($material->id));
    }
}
