<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $employee = Employee::where('email', $request->email)->first();
        if (!$employee || !Hash::check($request->password, $employee->password)) {
            if (!$request->expectsJson()) {
                return back()->withErrors([
                    'email' => 'The email or password is incorrect.',
                ])->onlyInput('email');
            }

            return response()->json(['message' => 'Incorrect password or email'], 401);
        }

        if (!$request->expectsJson()) {
            Auth::guard('web')->login($employee);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        $token = $employee->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Signed in.',
            'token' => $token,
            'user' => [
                'id' => $employee->emp_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'created_at' => $employee->created_at,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
