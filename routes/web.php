<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('signin');

Route::get('/dashboard', [EmployeeController::class, 'index'])->name('dashboard');

Route::resource('employees', EmployeeController::class)
    ->parameters(['employees' => 'employee']);
    