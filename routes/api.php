<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TripExportController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    //  Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);

    // Auth Actions
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Core Fleet Management
    Route::get('vehicles/alerts/maintenance', [VehicleController::class, 'maintenanceAlerts']);
    Route::apiResource('vehicles', VehicleController::class);
    Route::post('vehicles/{vehicle}/complete-service', [VehicleController::class, 'completeService']);
    Route::apiResource('drivers', DriverController::class);

    // Trip Lifecycle Endpoints
    Route::get('trips/export/csv', [TripExportController::class, 'exportCsv']);
    Route::apiResource('trips', TripController::class);
    Route::post('trips/{trip}/cancel', [TripController::class, 'cancel']);
});
