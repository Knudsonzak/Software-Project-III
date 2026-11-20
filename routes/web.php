<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLeaveController;

Route::get('/admin/leaves', [AdminLeaveController::class, 'index'])->name('admin.leaves.index');
Route::patch('/admin/leaves/{leaveForm}/approve', [AdminLeaveController::class, 'approve'])->name('admin.leaves.approve');
Route::patch('/admin/leaves/{leaveForm}/reject', [AdminLeaveController::class, 'reject'])->name('admin.leaves.reject');
Route::patch('/admin/leaves/{leaveForm}/status', [AdminLeaveController::class, 'updateStatus'])->name('admin.leaves.status');

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
