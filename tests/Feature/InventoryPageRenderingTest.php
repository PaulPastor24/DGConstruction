<?php

namespace Tests\Feature;

use App\Models\Tool;
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
}
