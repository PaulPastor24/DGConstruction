<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackUserPresence;
use App\Models\AttendanceScheduleRule;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceScheduleRuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(TrackUserPresence::class);
        (require database_path('migrations/2026_08_10_000001_create_attendance_schedule_rules_table.php'))->up();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('attendance_schedule_rules');
        parent::tearDown();
    }

    public function test_attendance_managers_can_save_replace_update_and_delete_rules(): void
    {
        foreach (['admin', 'administrator', 'engineer'] as $role) {
            $user = new User(['role' => $role, 'is_active' => true]);
            $user->user_id = 1;
            $this->actingAs($user);

            $data = [
                'role' => 'worker',
                'start_time' => '08:00',
                'end_time' => '18:00',
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
            ];
            $this->from('/admin/attendance')
                ->post(route('admin.attendance.schedules.store'), $data)
                ->assertRedirect('/admin/attendance')
                ->assertSessionHas('success');

            $data['start_time'] = '09:00';
            $this->post(route('admin.attendance.schedules.store'), $data)
                ->assertSessionHasNoErrors();
            $this->assertDatabaseCount('attendance_schedule_rules', 1);
            $this->assertDatabaseHas('attendance_schedule_rules', ['role' => 'worker', 'start_time' => '09:00:00']);

            $rule = AttendanceScheduleRule::firstOrFail();
            $data['end_time'] = '19:00';
            $this->put(route('admin.attendance.schedules.update', $rule), $data)
                ->assertRedirect('/admin/attendance')
                ->assertSessionHasNoErrors();
            $this->assertSame('19:00:00', $rule->fresh()->end_time);

            $this->delete(route('admin.attendance.schedules.destroy', $rule))
                ->assertRedirect('/admin/attendance');
            $this->assertDatabaseCount('attendance_schedule_rules', 0);
        }
    }

    public function test_client_cannot_save_rules(): void
    {
        $user = new User(['role' => 'client', 'is_active' => true]);
        $user->user_id = 1;
        $this->actingAs($user)
            ->post(route('admin.attendance.schedules.store'), [
                'role' => 'worker', 'start_time' => '08:00', 'end_time' => '18:00',
            ])
            ->assertRedirect('/login');
        $this->assertDatabaseCount('attendance_schedule_rules', 0);
    }
}
