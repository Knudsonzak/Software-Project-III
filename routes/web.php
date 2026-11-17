<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [EmployeeController::class, 'index']);
