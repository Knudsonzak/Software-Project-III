<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_directory_remains_available(): void
    {
        $this->actingAs($this->createAdmin())->get('/employees')
            ->assertOk()
            ->assertSee('Employee directory');
    }

    public function test_dashboard_displays_current_workforce_and_payroll_data(): void
    {
        $admin = $this->createAdmin();
        $now = now();
        $departmentId = DB::table('departments')->insertGetId([
            'department_name' => 'Engineering',
            'created_at' => $now,
            'updated_at' => $now,
        ], 'department_id');
        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Taylor Employee',
            'email' => 'taylor@example.com',
            'phone' => '555-0100',
            'password' => 'hashed-password',
            'salary' => 65000,
            'department_id' => $departmentId,
            'role' => 'employee',
            'created_at' => $now,
            'updated_at' => $now,
        ], 'emp_id');
        DB::table('time_logs')->insert([
            'emp_id' => $employeeId,
            'clock_in' => $now->copy()->subHour(),
            'break_minutes' => 0,
            'status' => 'approved',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $leaveTypeId = DB::table('leave_types')->insertGetId([
            'name' => 'Annual',
            'default_days' => 15,
            'created_at' => $now,
            'updated_at' => $now,
        ], 'leave_type_id');
        DB::table('leave_forms')->insert([
            'emp_id' => $employeeId,
            'start_date' => $now->toDateString(),
            'end_date' => $now->copy()->addDay()->toDateString(),
            'status' => 'pending',
            'reason' => 'Personal leave',
            'leave_type_id' => $leaveTypeId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payrolls')->insert([
            'emp_id' => $employeeId,
            'base_salary' => 5000,
            'overtime_hours' => 0,
            'overtime_pay' => 0,
            'deductions' => 0,
            'net_salary' => 5000,
            'payment_status' => 'paid',
            'paid_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Workforce Dashboard')
            ->assertSee('Total employees')
            ->assertSee('$5,000.00')
            ->assertSee('Absent today')
            ->assertSee('Engineering')
            ->assertSee('Taylor Employee');
    }

    public function test_employee_does_not_see_admin_dashboard_metrics(): void
    {
        $employee = Employee::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => 'password123',
            'salary' => 50000,
            'role' => 'employee',
        ]);

        $this->actingAs($employee)->get('/dashboard')
            ->assertOk()
            ->assertSee('My leave requests')
            ->assertDontSee('Net payroll this month');
    }

    private function createAdmin(): Employee
    {
        return Employee::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'salary' => 50000,
            'role' => 'admin',
        ]);
    }
}
