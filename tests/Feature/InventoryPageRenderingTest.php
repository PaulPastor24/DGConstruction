<?php

namespace Tests\Feature;

use App\Models\Tool;
use App\Models\Client;
use App\Models\Material;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\ToolDeduction;
use App\Models\ToolLoan;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryPageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_management_roles_can_render_inventory_and_tools_with_empty_ledgers(): void
    {
        foreach (['engineer', 'admin', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (['inventory', 'tools'] as $tab) {
                $this->get(route('admin.inventory', ['view' => $tab]))->assertOk()
                    ->assertViewHas('activeToolLoans', fn ($loans) => $loans->total() === 0)
                    ->assertViewHas('allToolDeductions', fn ($deductions) => $deductions->total() === 0);
            }
        }
    }

    public function test_inventory_renders_borrowed_tools_and_loss_deductions(): void
    {
        $worker = new Worker(['first_name' => 'Test', 'last_name' => 'Worker', 'trade' => 'Carpenter', 'is_active' => true]);
        // SQLite keeps the legacy required full_name column in the migration chain.
        $worker->forceFill(['full_name' => 'Test Worker'])->save();
        $borrowed = Tool::create(['tool_code' => 'RENDER-1', 'name' => 'Borrowed drill', 'status' => 'in_use', 'current_borrower_worker_id' => $worker->worker_id]);
        ToolLoan::create(['tool_id' => $borrowed->id, 'worker_id' => $worker->worker_id,
            'borrowed_at' => now(), 'expected_return_date' => now()->addWeek(), 'condition_at_issue' => 'good', 'status' => 'borrowed']);
        $lost = Tool::create(['tool_code' => 'RENDER-2', 'name' => 'Lost hammer', 'status' => 'lost']);
        $loan = ToolLoan::create(['tool_id' => $lost->id, 'worker_id' => $worker->worker_id,
            'borrowed_at' => now()->subDay(), 'condition_at_issue' => 'good', 'status' => 'lost']);
        ToolDeduction::create(['tool_id' => $lost->id, 'tool_loan_id' => $loan->id,
            'worker_id' => $worker->worker_id, 'amount' => 250, 'reason' => 'lost', 'status' => 'approved']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.inventory', ['view' => 'tools']))->assertOk()
            ->assertSee('Borrowed drill')->assertSee('Lost hammer')->assertSee('Test Worker')
            ->assertViewHas('activeToolLoans', fn ($loans) => $loans->total() === 1)
            ->assertViewHas('allToolDeductions', fn ($deductions) => $deductions->total() === 1);
    }

    public function test_inventory_still_renders_when_optional_tool_tables_are_absent(): void
    {
        Schema::drop('tool_deductions');
        Schema::drop('tool_loans');
        Schema::drop('tools');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.inventory'))->assertOk()
            ->assertViewHas('activeToolLoans', fn ($loans) => $loans->total() === 0)
            ->assertViewHas('allToolDeductions', fn ($deductions) => $deductions->total() === 0);
    }

    public function test_all_management_roles_can_clear_project_scope_and_view_warehouse_stock(): void
    {
        $owner = User::factory()->create(['role' => 'engineer']);
        $client = Client::create(['user_id' => User::factory()->create(['role' => 'client'])->user_id]);
        $project = Project::create([
            'project_name' => 'Previously selected project', 'project_location' => 'Test site',
            'client_id' => $client->client_id, 'engineer_id' => $owner->user_id,
            'start_date' => now(), 'target_end_date' => now()->addMonth(), 'status' => 'ongoing',
        ]);
        $otherProject = $project->replicate();
        $otherProject->project_name = 'Another project'; $otherProject->save();
        $allocated = Material::create(['name' => 'Selected project cement', 'unit' => 'bag', 'current_stock' => 50]);
        $otherMaterial = Material::create(['name' => 'Other project steel', 'unit' => 'piece', 'current_stock' => 30]);
        $warehouse = Material::create(['name' => 'Warehouse only paint', 'unit' => 'can', 'current_stock' => 20]);
        foreach ([[$project, $allocated], [$otherProject, $otherMaterial]] as [$target, $material]) {
            ProjectMaterial::create(['project_id' => $target->project_id, 'material_id' => $material->id,
                'planned_quantity' => 10, 'used_quantity' => 0, 'unit' => $material->unit]);
        }
        foreach (['engineer', 'admin', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->withSession(['admin_selected_project_id' => $project->project_id]);
            $scoped = $this->get(route('admin.inventory', ['project_id' => $project->project_id]))->assertOk();
            $this->assertSame([$allocated->id], $scoped->viewData('materials')->pluck('id')->all());
            foreach ([['project_id' => ''], []] as $query) {
                $all = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                    ->get(route('admin.inventory', $query))->assertOk()
                    ->assertSee('All projects / warehouse inventory')
                    ->assertSessionHas('admin_selected_project_id', $project->project_id);
                $this->assertEqualsCanonicalizing([$allocated->id, $otherMaterial->id, $warehouse->id],
                    $all->viewData('materials')->pluck('id')->all());
            }
        }
    }

    public function test_warehouse_pagination_does_not_restore_the_timeline_project_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::create(['user_id' => User::factory()->create(['role' => 'client'])->user_id]);
        $project = Project::create(['project_name' => 'Unrelated timeline project', 'project_location' => 'Site',
            'client_id' => $client->client_id, 'engineer_id' => $admin->user_id,
            'start_date' => now(), 'target_end_date' => now()->addMonth(), 'status' => 'ongoing']);
        foreach (range(1, 11) as $index) Material::create(['name' => 'Warehouse material '.$index, 'unit' => 'piece', 'current_stock' => 10]);
        $response = $this->actingAs($admin)->withSession(['admin_selected_project_id' => $project->project_id])
            ->get(route('admin.inventory', ['material_page' => 2]))->assertOk();
        $this->assertSame(11, $response->viewData('materials')->total());
        $this->assertCount(1, $response->viewData('materials'));
    }
}
