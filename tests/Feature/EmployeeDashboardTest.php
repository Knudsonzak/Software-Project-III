<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_only_the_signed_in_employees_data(): void
    {
        Carbon::setTestNow('2026-11-20 12:00:00');
        $employee = $this->employee('employee@example.com');
        $other = $this->employee('other@example.com');
        $type = DB::table('leave_types')->insertGetId([
            'name' => 'Vacation',
            'default_days' => 10,
        ], 'leave_type_id');

        DB::table('time_logs')->insert([
            ['emp_id' => $employee->emp_id, 'clock_in' => '2026-11-20 08:00:00', 'clock_out' => '2026-11-20 10:00:00', 'break_minutes' => 30],
            ['emp_id' => $other->emp_id, 'clock_in' => '2026-11-20 00:00:00', 'clock_out' => '2026-11-20 10:00:00', 'break_minutes' => 0],
        ]);
        DB::table('leave_forms')->insert([
            ['emp_id' => $employee->emp_id, 'leave_type_id' => $type, 'start_date' => '2026-12-01', 'end_date' => '2026-12-02', 'status' => 'pending'],
            ['emp_id' => $other->emp_id, 'leave_type_id' => $type, 'start_date' => '2027-01-01', 'end_date' => '2027-01-02', 'status' => 'pending'],
        ]);
        DB::table('payrolls')->insert([
            ['emp_id' => $employee->emp_id, 'base_salary' => 1000, 'net_salary' => 1000, 'created_at' => now()],
            ['emp_id' => $other->emp_id, 'base_salary' => 9999, 'net_salary' => 9999, 'created_at' => now()],
        ]);

        $response = $this->actingAs($employee)->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('employee-dashboard')
            ->assertViewHas('monthHours', 1.5)
            ->assertViewHas('pendingLeaves', 1)
            ->assertViewHas('weekly', fn ($weekly) => $weekly->last()['hours'] === 1.5)
            ->assertSee('$1,000.00')
            ->assertDontSee('$9,999.00')
            ->assertSee('Dec 1, 2026')
            ->assertDontSee('Jan 1, 2027');

        Carbon::setTestNow();
    }

    public function test_clock_control_is_disabled_and_does_not_create_time_logs(): void
    {
        $employee = $this->employee('employee@example.com');

        $this->actingAs($employee)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('class="employee-clock-button" type="button" disabled', false)
            ->assertSee('Clock in and out is not available yet.');
        $this->post('/dashboard/clock')->assertNotFound();
        $this->assertDatabaseCount('time_logs', 0);
    }

    private function employee(string $email, string $role = 'employee'): Employee
    {
        return Employee::create([
            'name' => 'Test Employee',
            'email' => $email,
            'password' => 'password123',
            'salary' => 50000,
            'role' => $role,
        ]);
    }
}
