<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CardController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/users/register', [UserController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cards', [CardController::class, 'index']);
    Route::get('/cards/{card}', [CardController::class, 'show']);
    Route::post('/cards', [CardController::class, 'store']);
    Route::post('/cards/{card}/deposit', [CardController::class, 'deposit']);
    Route::patch('/cards/{card}/status', [CardController::class, 'changeStatus']);
    Route::patch('/cards/{card}', [CardController::class, 'update']);
    Route::delete('/cards/{card}', [CardController::class, 'destroy']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);
});
