<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\DriverStatus;
use App\Enums\VehicleStatus;
use App\Enums\TripStatus;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', VehicleStatus::AVAILABLE)->count();
        $onTripVehicles = Vehicle::where('status', VehicleStatus::ON_TRIP)->count();

        $totalDrivers = Driver::count();
        $availableDrivers = Driver::where('status', DriverStatus::AVAILABLE)->count();
        $onTripDrivers = Driver::where('status', DriverStatus::ON_TRIP)->count();

        $totalTrips = Trip::count();
        $inTransitTrips = Trip::where('status', TripStatus::IN_TRANSIT)->count();
        $completedTrips = Trip::where('status', TripStatus::COMPLETED)->count();
        $cancelledTrips = Trip::where('status', TripStatus::CANCELLED)->count();

        $totalDistance = Trip::where('status', TripStatus::COMPLETED)
            ->whereNotNull('end_odometer')
            ->selectRaw('SUM(end_odometer - start_odometer) as total_km')
            ->value('total_km') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'vehicles' => [
                    'total' => $totalVehicles,
                    'available' => $availableVehicles,
                    'on_trip' => $onTripVehicles,
                ],
                'drivers' => [
                    'total' => $totalDrivers,
                    'available' => $availableDrivers,
                    'on_trip' => $onTripDrivers,
                ],
                'trips' => [
                    'total' => $totalTrips,
                    'in_transit' => $inTransitTrips,
                    'completed' => $completedTrips,
                    'cancelled' => $cancelledTrips,
                ],
                'fleet_performance' => [
                    'total_distance_covered_km' => (int) $totalDistance,
                ],
            ],
        ], 200);
    }
}
