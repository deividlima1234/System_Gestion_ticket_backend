<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/admin-only', function () {
            return response()->json(['message' => 'Welcome Admin']);
        })->middleware('role:admin');

        Route::middleware(['role:admin'])->group(function () {
             Route::apiResource('users', \App\Http\Controllers\UserController::class);
        });

        Route::apiResource('tickets', \App\Http\Controllers\TicketController::class);
        Route::post('/tickets/{ticket}/comments', [\App\Http\Controllers\CommentController::class, 'store']);
        Route::get('/tickets/{ticket}/comments', [\App\Http\Controllers\CommentController::class, 'index']);
        Route::put('/tickets/{ticket}/assign', [\App\Http\Controllers\TicketController::class, 'assign']);
        
        Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'getStats']);

        Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show']);
        Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update']);
    });
});
