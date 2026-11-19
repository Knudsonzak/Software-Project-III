<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;

Route::post('/', [AuthController::class, 'login'])->name('login');
Route::get('/dashboard', [EmployeeController::class, 'index'])->name('dashboard');
Route::get('/employees/{id}', [EmployeeController::class, 'show'])
    ->whereNumber('id');
