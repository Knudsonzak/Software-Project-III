<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_and_submit_a_leave_request_for_their_own_account(): void
    {
        $employee = $this->employee('employee@example.com');
        $leaveTypeId = $this->createLeaveType();

        $this->actingAs($employee)
            ->get(route('leaves.index'))
            ->assertOk()
            ->assertSee('Submit Leave Request');

        $this->post(route('leaves.store'), [
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-12-01 00:00:00',
            'end_date' => '2026-12-03 00:00:00',
            'reason' => 'Family trip',
        ])->assertRedirect()
            ->assertSessionHas('success', 'Leave request submitted.');

        $this->assertDatabaseHas('leave_forms', [
            'emp_id' => $employee->emp_id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-12-01 00:00:00',
            'end_date' => '2026-12-03 00:00:00',
            'reason' => 'Family trip',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_submit_their_own_request_and_review_employee_requests(): void
    {
        $admin = $this->employee('admin@example.com', 'admin');
        $leaveTypeId = $this->createLeaveType();

        $this->actingAs($admin)
            ->get(route('leaves.index'))
            ->assertOk()
            ->assertSee('Submit Leave Request');

        $this->post(route('leaves.store'), [
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-12-10',
            'end_date' => '2026-12-10',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('leave_forms', [
            'emp_id' => $admin->emp_id,
            'leave_type_id' => $leaveTypeId,
            'status' => 'pending',
        ]);

        $this->get(route('admin.leaves.index'))
            ->assertOk()
            ->assertSee('Employee Leave Requests')
            ->assertSee('Request leave');
    }

    public function test_employee_cannot_access_admin_leave_review_or_change_status(): void
    {
        $employee = $this->employee('employee@example.com');
        $otherEmployee = $this->employee('other@example.com');
        $leaveTypeId = $this->createLeaveType();
        $formId = DB::table('leave_forms')->insertGetId([
            'emp_id' => $otherEmployee->emp_id,
            'leave_type_id' => $leaveTypeId,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-02',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'form_id');

        $this->actingAs($employee)
            ->get(route('admin.leaves.index'))
            ->assertForbidden();

        $this->patch(route('admin.leaves.approve', $formId))
            ->assertForbidden();

        $this->assertDatabaseHas('leave_forms', [
            'form_id' => $formId,
            'status' => 'pending',
        ]);
    }

    private function employee(string $email, string $role = 'employee'): Employee
    {
        return Employee::create([
            'name' => ucfirst($role) . ' User',
            'email' => $email,
            'password' => 'password123',
            'salary' => 50000,
            'role' => $role,
        ]);
    }

    private function createLeaveType(): int
    {
        return DB::table('leave_types')->insertGetId([
            'name' => 'Annual',
            'default_days' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'leave_type_id');
    }
}
