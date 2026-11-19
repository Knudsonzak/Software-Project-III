<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            if (!$request->expectsJson()) {
                return back()->withErrors([
                    'email' => 'The email or password is incorrect.',
                ])->onlyInput('email');
            }

            return response()->json(['message' => 'Incorrect password or email'], 401);
        }

        if (!$request->expectsJson()) {
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Signed in.',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'created_at', 'last_logged_in']),
        ], 200);
    }
}
