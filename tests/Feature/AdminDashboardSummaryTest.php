<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminDashboardController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDashboardSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function ($table) {
            $table->id('user_id');
            $table->string('role');
        });
        DB::table('users')->insert([
            ['user_id' => 1, 'role' => 'admin'],
            ['user_id' => 2, 'role' => 'supervisor'],
        ]);
        $this->actingAs(User::findOrFail(1));
    }

    public function test_worker_summary_counts_active_worker_records_instead_of_supervisor_accounts(): void
    {
        Schema::create('workers', function ($table) {
            $table->id('worker_id');
            $table->boolean('is_active');
        });
        DB::table('workers')->insert([
            ['is_active' => true], ['is_active' => true],
            ['is_active' => true], ['is_active' => true], ['is_active' => false],
        ]);

        $data = (new AdminDashboardController)->index()->getData();

        $this->assertSame(4, $data['stats']['total_workforce']);
    }

    public function test_worker_summary_defaults_to_zero_when_worker_table_is_missing(): void
    {
        $data = (new AdminDashboardController)->index()->getData();

        $this->assertSame(0, $data['stats']['total_workforce']);
    }

    public function test_recent_reports_include_the_ten_latest_submissions(): void
    {
        Schema::create('accomplishment_reports', function ($table) {
            $table->id('report_id');
            $table->integer('project_id')->nullable();
            $table->integer('phase_id')->nullable();
            $table->integer('submitted_by')->nullable();
            $table->date('report_date');
            $table->string('approval_status')->default('pending');
            $table->timestamps();
        });
        for ($day = 1; $day <= 12; $day++) {
            DB::table('accomplishment_reports')->insert([
                'report_date' => sprintf('2026-10-%02d', $day),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $reports = (new AdminDashboardController)->index()->getData()['recentReports'];

        $this->assertCount(10, $reports);
        $this->assertSame(12, $reports->first()->id);
        $this->assertSame(3, $reports->last()->id);
    }
}
