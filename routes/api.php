<?php

use App\Http\Controllers\V1\Auth\LoginController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\VehicleController;
use App\Http\Controllers\V1\VehicleTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::get('/vehicle-types', [VehicleTypeController::class, 'index']);
    Route::get('/vehicles', [VehicleController::class, 'index']); // Public vehicle listing
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']); // Public vehicle detail

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
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy']);

        // Unapproved vehicles route (must come before the approval route)
        Route::get('/vehicles/unapproved', [VehicleController::class, 'unapproved']);

        // Admin-only vehicle approval
        Route::patch('/vehicles/{id}/approve', [VehicleController::class, 'approve']);
    });
});

// Fallback for non-existent API endpoints
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found. Please check the URL and HTTP method.',
        'available_versions' => ['v1']
    ], 404);
});
