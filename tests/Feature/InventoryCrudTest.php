<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminDashboardController;
use App\Models\Material;
use App\Models\MaterialDelivery;
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
            $table->bigInteger('material_id');
            $table->bigInteger('project_id')->nullable();
            $table->decimal('quantity_used', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->date('usage_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('material_deliveries', function ($table) {
            $table->id('delivery_id');
            $table->bigInteger('material_id');
            $table->bigInteger('project_id')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->string('supplier_name')->nullable();
            $table->date('delivered_at')->nullable();
            $table->text('notes')->nullable();
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

    public function test_receive_stock_can_create_new_material_when_name_is_provided(): void
    {
        $request = Request::create('/admin/inventory/materials/receive', 'POST', [
            'material_name' => 'Aluminum Frames',
            'quantity_received' => 12,
            'received_date' => now()->toDateString(),
            'supplier' => 'Metro Supply',
            'remarks' => 'New material received',
        ]);

        $response = (new AdminDashboardController())->receiveStock($request, null);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals('Stock received successfully.', session('success'));

        $material = Material::query()->where('name', 'Aluminum Frames')->first();
        $this->assertNotNull($material);
        $this->assertSame('Unit', $material->unit);
        $this->assertEquals(12, (float) $material->current_stock);
        $this->assertSame('Metro Supply', $material->supplier);

        $delivery = MaterialDelivery::query()
            ->where('material_id', $material->id)
            ->latest('delivery_id')
            ->first();

        $this->assertNotNull($delivery);
        $this->assertEquals(12, (float) $delivery->quantity);
        $this->assertSame('Metro Supply', $delivery->supplier_name);
    }

    public function test_receive_stock_requires_material_selection_or_name(): void
    {
        $request = Request::create('/admin/inventory/materials/receive', 'POST', [
            'quantity_received' => 5,
            'received_date' => now()->toDateString(),
        ]);

        $response = (new AdminDashboardController())->receiveStock($request, null);

        $this->assertTrue($response->isRedirect());
        $this->assertSame('Please select a material or enter a new material name.', session('errors')->first('material_name'));
    }

    public function test_receive_stock_rejects_nonexistent_selected_material(): void
    {
        $request = Request::create('/admin/inventory/materials/receive', 'POST', [
            'material_id' => 9999,
            'quantity_received' => 5,
            'received_date' => now()->toDateString(),
        ]);

        $response = (new AdminDashboardController())->receiveStock($request, null);

        $this->assertTrue($response->isRedirect());
        $this->assertSame('The selected material does not exist.', session('errors')->first('material_id'));
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

        $delivery = MaterialDelivery::query()
            ->where('material_id', $material->id)
            ->latest('delivery_id')
            ->first();

        $this->assertNotNull($delivery);
        $this->assertEquals(15, (float) $delivery->quantity);
        $this->assertSame('Build Supply Co', $delivery->supplier_name);

        $deleteResponse = $controller->destroyMaterial($material);
        $this->assertTrue($deleteResponse->isRedirect());
        $this->assertEquals('Unable to delete. This material has already been used in project records.', session('error'));
        $this->assertNotNull(Material::query()->find($material->id));
    }
}
