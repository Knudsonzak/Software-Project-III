<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_attendance_by_date_range_and_employee_and_see_total_hours(): void
    {
        Carbon::setTestNow('2026-11-24 12:00:00');
        $admin = $this->employee('admin@example.com', 'admin');
        $alice = $this->employee('alice@example.com', 'employee', 'Alice Employee');
        $bob = $this->employee('bob@example.com', 'employee', 'Bob Employee');

        $this->insertLog($alice, '2026-11-24 08:00:00', null, 15, null);
        $this->insertLog($alice, '2026-11-23 08:00:00', '2026-11-23 16:00:00', 30, 7.5);
        $this->insertLog($bob, '2026-11-24 09:00:00', '2026-11-24 17:00:00', 0, 8);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index', [
                'start_date' => '2026-11-24',
                'end_date' => '2026-11-24',
                'employee_id' => $alice->emp_id,
            ]))
            ->assertOk()
            ->assertSee('Attendance Management')
            ->assertSee('Alice Employee')
            ->assertDontSee('Bob Employee')
            ->assertSee('3.75')
            ->assertSee('In progress');

        Carbon::setTestNow();
    }

    public function test_admin_attendance_defaults_to_today_and_lists_all_employee_records(): void
    {
        Carbon::setTestNow('2026-11-24 12:00:00');
        $admin = $this->employee('admin@example.com', 'admin');
        $alice = $this->employee('alice@example.com', 'employee', 'Alice Employee');
        $bob = $this->employee('bob@example.com', 'employee', 'Bob Employee');
        $this->insertLog($alice, '2026-11-24 08:00:00', '2026-11-24 10:00:00', 0, 2);
        $this->insertLog($bob, '2026-11-24 09:00:00', '2026-11-24 11:00:00', 0, 2);

        $this->actingAs($admin)
            ->get(route('admin.attendance.index'))
            ->assertOk()
            ->assertSee('value="2026-11-24"', false)
            ->assertSee('Alice Employee')
            ->assertSee('Bob Employee');

        Carbon::setTestNow();
    }

    public function test_regular_employee_is_denied_access_to_admin_attendance(): void
    {
        $employee = $this->employee('employee@example.com');

        $this->actingAs($employee)
            ->get(route('admin.attendance.index'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_reports_present_and_absent_employee_counts(): void
    {
        Carbon::setTestNow('2026-11-24 12:00:00');
        $admin = $this->employee('admin@example.com', 'admin');
        $presentEmployee = $this->employee('present@example.com', 'employee', 'Present Employee');
        $this->employee('absent-one@example.com', 'employee', 'Absent One');
        $this->employee('absent-two@example.com', 'employee', 'Absent Two');
        $this->insertLog($presentEmployee, '2026-11-24 08:00:00', null, 0, null);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('employeeCount', 3)
            ->assertViewHas('present', 1)
            ->assertViewHas('absent', 2)
            ->assertSee('Present today')
            ->assertSee('Absent today');

        Carbon::setTestNow();
    }

    private function employee(string $email, string $role = 'employee', ?string $name = null): Employee
    {
        return Employee::create([
            'name' => $name ?? ucfirst($role) . ' User',
            'email' => $email,
            'password' => 'password123',
            'salary' => 50000,
            'role' => $role,
        ]);
    }

    private function insertLog(Employee $employee, string $clockIn, ?string $clockOut, int $breakMinutes, ?float $hours): void
    {
        DB::table('time_logs')->insert([
            'emp_id' => $employee->emp_id,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_minutes' => $breakMinutes,
            'hours' => $hours,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
