<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminDashboardController;
use App\Models\Material;
use App\Models\MaterialDelivery;
use App\Models\MaterialUsage;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\MaterialRequest;
use App\Models\Tool;
use App\Models\ToolDeduction;
use App\Models\ToolLoan;
use App\Models\Worker;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class InventoryCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->dropInventoryFeatureTables();
        Schema::dropIfExists('material_usages');
        Schema::dropIfExists('material_deliveries');
        Schema::dropIfExists('project_materials');
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
            $table->string('status')->default('planning');
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

    protected function tearDown(): void
    {
        $this->dropInventoryFeatureTables();
        parent::tearDown();
    }

    private function dropInventoryFeatureTables(): void
    {
        foreach (['tool_deductions', 'tool_loans', 'tools', 'material_requests', 'project_materials', 'workers', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
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

    public function test_inventory_materials_are_limited_to_ten_per_page(): void
    {
        for ($index = 1; $index <= 11; $index++) {
            Material::create([
                'name' => 'Material ' . $index,
                'category' => 'General',
                'unit' => 'Piece',
                'current_stock' => 20,
                'minimum_stock_level' => 5,
            ]);
        }

        $response = (new AdminDashboardController())->inventory(Request::create('/admin/inventory', 'GET'));
        $materials = $response->getData()['materials'];

        $this->assertSame(10, $materials->perPage());
        $this->assertSame(2, $materials->lastPage());
        $this->assertCount(10, $materials);
    }

    public function test_inventory_tables_page_all_requested_views_at_ten_rows(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('workers', function (Blueprint $table) {
            $table->increments('worker_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('trade')->nullable();
            $table->timestamps();
        });
        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('material_id');
            $table->decimal('planned_quantity', 12, 2)->default(0);
            $table->decimal('used_quantity', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->timestamps();
        });
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('material_id');
            $table->unsignedBigInteger('requested_by');
            $table->string('status')->default('pending');
            $table->decimal('requested_quantity', 12, 2);
            $table->timestamps();
        });
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('tool_code');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('type')->default('tool');
            $table->string('unit')->nullable();
            $table->string('condition')->default('good');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('available');
            $table->unsignedBigInteger('current_borrower_worker_id')->nullable();
            $table->timestamps();
        });
        Schema::create('tool_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tool_id');
            $table->unsignedBigInteger('worker_id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->date('expected_return_date')->nullable();
            $table->string('condition_at_issue')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('borrowed');
            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('condition_at_return')->nullable();
            $table->timestamps();
        });
        Schema::create('tool_deductions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tool_loan_id');
            $table->unsignedBigInteger('tool_id');
            $table->unsignedBigInteger('worker_id');
            $table->decimal('amount', 12, 2);
            $table->string('reason');
            $table->text('remarks')->nullable();
            $table->string('status')->default('approved');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'name' => 'Inventory Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'user_id');
        $worker = Worker::create(['first_name' => 'Taylor', 'last_name' => 'Worker']);
        $project = Project::create(['project_name' => 'Pagination Project']);

        for ($index = 1; $index <= 11; $index++) {
            $material = Material::create([
                'name' => 'Pagination Material ' . $index,
                'category' => 'General',
                'unit' => 'Piece',
            ]);
            MaterialUsage::create([
                'material_id' => $material->id,
                'quantity_used' => 1,
                'usage_date' => now()->toDateString(),
            ]);
            ProjectMaterial::create([
                'project_id' => $project->project_id,
                'material_id' => $material->id,
                'planned_quantity' => 10,
                'used_quantity' => 1,
                'unit' => 'Piece',
            ]);
            MaterialRequest::create([
                'project_id' => $project->project_id,
                'material_id' => $material->id,
                'requested_by' => $userId,
                'status' => 'pending',
                'requested_quantity' => 2,
            ]);

            if ($index <= 10) {
                $activeTool = Tool::create([
                    'tool_code' => 'ACTIVE-' . $index,
                    'name' => 'Active Tool ' . $index,
                    'status' => 'in_use',
                    'current_borrower_worker_id' => $worker->worker_id,
                ]);
                ToolLoan::create([
                    'tool_id' => $activeTool->id,
                    'worker_id' => $worker->worker_id,
                    'status' => 'borrowed',
                    'borrowed_at' => now(),
                ]);
                ToolLoan::create([
                    'tool_id' => $activeTool->id,
                    'worker_id' => $worker->worker_id,
                    'status' => 'returned',
                    'borrowed_at' => now()->subDays(2),
                    'returned_at' => now()->subDay(),
                    'condition_at_return' => 'good',
                ]);
            } else {
                $lostTool = Tool::create([
                    'tool_code' => 'LOST-' . $index,
                    'name' => 'Lost Tool ' . $index,
                    'status' => 'lost',
                ]);
                $lostLoan = ToolLoan::create([
                    'tool_id' => $lostTool->id,
                    'worker_id' => $worker->worker_id,
                    'status' => 'lost',
                    'borrowed_at' => now()->subDay(),
                ]);
                ToolDeduction::create([
                    'tool_loan_id' => $lostLoan->id,
                    'tool_id' => $lostTool->id,
                    'worker_id' => $worker->worker_id,
                    'amount' => 100,
                    'reason' => 'lost',
                    'status' => 'approved',
                ]);
            }
        }

        $data = (new AdminDashboardController())
            ->inventory(Request::create('/admin/inventory', 'GET'))
            ->getData();

        foreach (['materials', 'usageLogs', 'allocatedMaterials', 'materialRequests', 'tools', 'toolHistory'] as $table) {
            $this->assertSame(10, $data[$table]->perPage(), $table . ' should display at most ten rows.');
            $this->assertLessThanOrEqual(10, $data[$table]->count(), $table . ' should not display more than ten rows.');
            if ($data[$table]->total() > 10) {
                $this->assertSame((int) ceil($data[$table]->total() / 10), $data[$table]->lastPage(), $table . ' should paginate all rows beyond the first ten.');
                $this->assertTrue($data[$table]->hasPages(), $table . ' should expose pagination when more than ten rows exist.');
            }
        }

        $historyPageTwoRequest = Request::create('/admin/inventory?view=tools&tool_history_page=2', 'GET');
        Paginator::currentPageResolver(fn ($pageName) => $historyPageTwoRequest->query($pageName, 1));
        $historyPageTwoView = (new AdminDashboardController())->inventory($historyPageTwoRequest);
        Paginator::currentPageResolver(fn ($pageName) => request()->query($pageName, 1));
        $historyPageTwo = $historyPageTwoView->getData()['toolHistory'];
        $this->assertSame(2, $historyPageTwo->currentPage());
        $this->assertSame(10, $historyPageTwo->count());
        $this->assertSame(21, $historyPageTwo->total());
        $lostHistory = $historyPageTwo->firstWhere('status', 'lost');
        $this->assertNotNull($lostHistory, 'The second history page should include the recorded lost loan.');
        $this->assertSame('lost', $lostHistory->deduction->reason);
        $this->assertSame('approved', $lostHistory->deduction->status);
        $this->assertSame('Lost Tool 11', $lostHistory->tool->name);
        $this->assertStringContainsString('tool_history_page=1', $historyPageTwo->url(1));
        $this->assertStringContainsString('#toolHistoryModal', $historyPageTwo->url(1));
        $this->assertStringContainsString('tool_history_page=2', $historyPageTwo->url(2));
        $this->assertStringContainsString('#toolHistoryModal', $historyPageTwo->url(2));

        $lostTool = Tool::with('loans.worker')->where('status', 'lost')->firstOrFail();
        $lostLoan = $lostTool->loans->first();
        $this->assertNull($lostTool->currentBorrower);
        $this->assertSame('Taylor Worker', $lostLoan->worker->full_name);
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

    public function test_allocated_materials_can_be_filtered_by_project_and_category(): void
    {
        Schema::create('project_materials', function ($table) {
            $table->id();
            $table->unsignedInteger('project_id');
            $table->unsignedBigInteger('material_id');
            $table->decimal('planned_quantity', 12, 2)->default(0);
            $table->decimal('used_quantity', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        $cement = Material::create([
            'name' => 'Portland Cement',
            'category' => 'Masonry',
            'unit' => 'Bag',
        ]);
        $steel = Material::create([
            'name' => 'Steel Rebar',
            'category' => 'Structural',
            'unit' => 'Ton',
        ]);
        $project = Project::create(['project_name' => 'North Wing']);
        $otherProject = Project::create(['project_name' => 'South Wing']);
        $archivedProject = Project::create(['project_name' => 'Archived Wing', 'status' => 'archived']);

        ProjectMaterial::create([
            'project_id' => $project->project_id,
            'material_id' => $cement->id,
            'planned_quantity' => 30,
            'used_quantity' => 8,
            'unit' => 'Bag',
        ]);
        ProjectMaterial::create([
            'project_id' => $otherProject->project_id,
            'material_id' => $steel->id,
            'planned_quantity' => 12,
            'used_quantity' => 2,
            'unit' => 'Ton',
        ]);

        $response = (new AdminDashboardController())->inventory(Request::create('/admin/inventory', 'GET', [
            'view' => 'allocated',
            'allocated_search' => 'Cement',
            'allocated_project_id' => $project->project_id,
            'allocated_category' => 'Masonry',
        ]));
        $data = $response->getData();

        $this->assertCount(1, $data['allocatedMaterials']);
        $this->assertSame($project->project_id, $data['allocatedMaterials']->first()->project_id);
        $this->assertSame($cement->id, $data['allocatedMaterials']->first()->material_id);
        $this->assertNotContains($archivedProject->project_id, $data['projects']->pluck('project_id')->all());
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
