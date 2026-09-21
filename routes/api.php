<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TripExportController;
use App\Http\Controllers\Api\TripLocationController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Auth Actions
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Operational listings
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->middleware('permission:view-dashboard');
    Route::get('trips/export/csv', [TripExportController::class, 'exportCsv'])->middleware('permission:view-financials');
    Route::get('vehicles/alerts/maintenance', [VehicleController::class, 'maintenanceAlerts']);

    // Resource read-only routes
    Route::get('vehicles', [VehicleController::class, 'index']);
    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show']);
    Route::get('drivers', [DriverController::class, 'index']);
    Route::get('drivers/{driver}', [DriverController::class, 'show']);

    // Trips management with granular permissions
    Route::get('trips', [TripController::class, 'index']);
    Route::get('trips/{trip}', [TripController::class, 'show']);
    Route::post('trips', [TripController::class, 'store'])->middleware('permission:dispatch-trips');
    Route::put('trips/{trip}', [TripController::class, 'update'])->middleware('permission:complete-trips');
    Route::post('trips/{trip}/cancel', [TripController::class, 'cancel'])->middleware('permission:cancel-trips');
    Route::post('trips/{trip}/location', [TripLocationController::class, 'store']);
    Route::get('trips/{trip}/trail', [TripLocationController::class, 'history']);

    // Admin-Only Asset Control
    Route::middleware('role:admin')->group(function () {
        Route::post('vehicles', [VehicleController::class, 'store']);
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy']);
        Route::post('vehicles/{vehicle}/complete-service', [VehicleController::class, 'completeService']);

        Route::post('drivers', [DriverController::class, 'store']);
        Route::put('drivers/{driver}', [DriverController::class, 'update']);
        Route::delete('drivers/{driver}', [DriverController::class, 'destroy']);
    });
});
