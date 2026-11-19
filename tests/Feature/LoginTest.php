<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_sign_in_and_be_reloaded_from_the_session(): void
    {
        $employee = $this->createEmployee();

        $this->post(route('signin'), [
            'email' => $employee->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        Auth::forgetGuards();
        $this->get(route('dashboard'))->assertOk();
        $this->assertAuthenticatedAs($employee, 'web');
    }

    public function test_user_table_credentials_do_not_authenticate_an_employee(): void
    {
        $employee = $this->createEmployee();
        User::factory()->create([
            'email' => $employee->email,
            'password' => 'user-password',
        ]);

        $this->from(route('login'))->post(route('signin'), [
            'email' => $employee->email,
            'password' => 'user-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_json_login_returns_an_employee_token(): void
    {
        $employee = $this->createEmployee();

        $this->postJson(route('signin'), [
            'email' => $employee->email,
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('user.id', $employee->emp_id)
            ->assertJsonPath('user.email', $employee->email)
            ->assertJsonStructure(['token']);
    }

    private function createEmployee(): Employee
    {
        return Employee::create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => 'password123',
            'salary' => 50000,
            'role' => 'employee',
        ]);
    }
}
