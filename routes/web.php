<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('signin');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return auth()->user()->role === 'admin'
            ? app(AdminDashboardController::class)->index()
            : app(EmployeeController::class)->index();
    })->name('dashboard');

    Route::resource('employees', EmployeeController::class)
        ->parameters(['employees' => 'employee']);
});
