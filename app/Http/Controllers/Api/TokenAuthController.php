<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class TokenAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $employee = Employee::where('email', $validated['email'])->first();

        if (! $employee || ! Hash::check($validated['password'], $employee->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = $employee->createToken($validated['device_name'] ?? 'api-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'employee' => $this->employeeData($employee),
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'employee' => $this->employeeData($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            return response()->json(['message' => 'A bearer token is required.'], 400);
        }

        $token->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * @return array{emp_id: int, name: string, email: string, role: string}
     */
    private function employeeData(Employee $employee): array
    {
        return [
            'emp_id' => $employee->emp_id,
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => $employee->role,
        ];
    }
}
