<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

Route::get('/', function () {
    return view('welcome');
<<<<<<< HEAD
})->name('main');
=======
});

Route::get('/dashboard', [EmployeeController::class, 'index']);
>>>>>>> 1c9bfdd604d5c6484f8a660028117008f493ac7c
