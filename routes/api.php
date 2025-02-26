<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VehicleTypeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/vehicle-types', [VehicleTypeController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout route
    Route::post('/logout', [LoginController::class, 'logout']);

    // Admin-only user creation
    Route::post('/users', [UserController::class, 'store']);

    // Vehicle routes (authenticated users only)
    Route::apiResource('vehicles', VehicleController::class);

    // Admin-only vehicle approval
    Route::patch('/vehicles/{id}/approve', [VehicleController::class, 'approve']);
});
