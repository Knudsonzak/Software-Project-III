<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeTokenAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_login_and_receive_a_bearer_token(): void
    {
        $employee = $this->createEmployee();

        $response = $this->postJson('/api/login', [
            'email' => $employee->email,
            'password' => 'correct-password',
            'device_name' => 'test-device',
        ]);

        $response->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('employee.emp_id', $employee->emp_id)
            ->assertJsonMissingPath('employee.salary')
            ->assertJsonMissingPath('employee.password');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $employee->emp_id,
            'name' => 'test-device',
        ]);
    }

    public function test_invalid_employee_credentials_are_rejected(): void
    {
        $employee = $this->createEmployee();

        $this->postJson('/api/login', [
            'email' => $employee->email,
            'password' => 'incorrect-password',
        ])->assertUnauthorized();
    }

    public function test_bearer_token_authenticates_the_employee_and_can_be_revoked(): void
    {
        $employee = $this->createEmployee();
        $plainTextToken = $employee->createToken('test-device')->plainTextToken;

        $this->withToken($plainTextToken)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('employee.emp_id', $employee->emp_id)
            ->assertJsonPath('employee.role', 'employee')
            ->assertJsonMissingPath('employee.salary')
            ->assertJsonMissingPath('employee.password');

        $this->withToken($plainTextToken)
            ->postJson('/api/logout')
            ->assertOk();

        $this->withToken($plainTextToken)
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_employee_details_require_a_bearer_token(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    private function createEmployee(): Employee
    {
        $employee = new Employee();
        $employee->name = 'Test Employee';
        $employee->email = 'employee@example.com';
        $employee->password = Hash::make('correct-password');
        $employee->salary = 50000;
        $employee->role = 'employee';
        $employee->save();

        return $employee;
    }
}
