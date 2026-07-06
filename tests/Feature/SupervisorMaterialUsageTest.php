<?php

namespace Tests\Feature;

use App\Models\ConstructionPhase;
use App\Models\Material;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupervisorMaterialUsageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('supervisor');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('attendance_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function ($table) {
            $table->id('project_id');
            $table->string('project_name');
            $table->string('status')->default('ongoing');
            $table->timestamps();
        });

        Schema::create('construction_phases', function ($table) {
            $table->id('phase_id');
            $table->unsignedBigInteger('project_id');
            $table->string('phase_name');
            $table->integer('phase_order')->default(1);
            $table->string('status')->default('in_progress');
            $table->timestamps();
        });

        Schema::create('materials', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        Schema::create('project_materials', function ($table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('material_id');
            $table->decimal('planned_quantity', 12, 2)->default(0);
            $table->decimal('used_quantity', 12, 2)->default(0);
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        Schema::create('material_usages', function ($table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('phase_id');
            $table->unsignedBigInteger('material_id');
            $table->decimal('quantity_used', 12, 2);
            $table->string('unit')->nullable();
            $table->date('usage_date');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->string('site_photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('project_supervisors', function ($table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->timestamps();
        });
    }

    public function test_supervisor_can_record_usage_when_material_has_no_planned_quantity(): void
    {
        $user = User::create([
            'name' => 'Supervisor One',
            'email' => 'supervisor@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project',
            'status' => 'ongoing',
        ]);

        DB::table('project_supervisors')->insert([
            'project_id' => $project->project_id,
            'supervisor_id' => $user->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $phase = ConstructionPhase::create([
            'project_id' => $project->project_id,
            'phase_name' => 'Foundation',
            'phase_order' => 1,
            'status' => 'in_progress',
        ]);

        $material = Material::create([
            'name' => 'Cement',
            'unit' => 'bags',
        ]);

        $response = $this->actingAs($user)->post(route('supervisor.materials.log'), [
            'form_type' => 'usage',
            'project_id' => $project->project_id,
            'phase_id' => $phase->phase_id,
            'material_id' => $material->id,
            'quantity_used' => 5,
            'usage_date' => '2026-07-05',
            'remarks' => 'Used on site',
        ]);

        $response->assertRedirectContains(route('supervisor.materials', ['project_id' => $project->project_id]));
        $this->assertDatabaseHas('material_usages', [
            'project_id' => $project->project_id,
            'phase_id' => $phase->phase_id,
            'material_id' => $material->id,
            'quantity_used' => '5.0',
        ]);
        $this->assertDatabaseHas('project_materials', [
            'project_id' => $project->project_id,
            'material_id' => $material->id,
            'used_quantity' => '5.0',
        ]);
    }

    public function test_supervisor_material_metrics_include_out_of_stock_items(): void
    {
        $user = User::create([
            'name' => 'Supervisor Two',
            'email' => 'supervisor2@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project Two',
            'status' => 'ongoing',
        ]);

        DB::table('project_supervisors')->insert([
            'project_id' => $project->project_id,
            'supervisor_id' => $user->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $material = Material::create([
            'name' => 'Steel',
            'unit' => 'tons',
        ]);

        DB::table('project_materials')->insert([
            'project_id' => $project->project_id,
            'material_id' => $material->id,
            'planned_quantity' => 5,
            'used_quantity' => 5,
            'unit' => 'tons',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('supervisor.materials', ['project_id' => $project->project_id]));

        $response->assertOk();
        $response->assertSee('Out of Stock');
        $response->assertSee('1');
    }

    public function test_supervisor_delivery_logging_is_rejected_when_not_supported(): void
    {
        $user = User::create([
            'name' => 'Supervisor Two',
            'email' => 'supervisor2@example.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $project = Project::create([
            'project_name' => 'Test Project Two',
            'status' => 'ongoing',
        ]);

        DB::table('project_supervisors')->insert([
            'project_id' => $project->project_id,
            'supervisor_id' => $user->user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $material = Material::create([
            'name' => 'Steel',
            'unit' => 'tons',
        ]);

        $response = $this->actingAs($user)->post(route('supervisor.materials.log'), [
            'form_type' => 'delivery',
            'project_id' => $project->project_id,
            'material_id' => $material->id,
            'quantity' => 3,
            'unit' => 'tons',
            'supplier_name' => 'Test Supplier',
        ]);

        $response->assertSessionHas('error', 'Material delivery logging is no longer available.');
        $this->assertDatabaseMissing('project_materials', [
            'project_id' => $project->project_id,
            'material_id' => $material->id,
        ]);
    }
}
