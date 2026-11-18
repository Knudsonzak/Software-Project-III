<?php

use App\Http\Controllers\Api\TokenAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [TokenAuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [TokenAuthController::class, 'me']);
    Route::post('/logout', [TokenAuthController::class, 'logout']);
});
