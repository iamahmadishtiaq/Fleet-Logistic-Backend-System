<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\TripStatus;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;

class FleetTrackingController extends Controller
{
    // Get live location and telemetry for all active trips

    public function liveMap(): JsonResponse
    {
        $activeTrips = Trip::with([
            'vehicle:id,plate_number,make,model,type',
            'driver:id,name,phone',
            'latestLocation'
        ])
        ->where('status', TripStatus::IN_TRANSIT)
        ->latest('started_at')
        ->get();

        $mapData = $activeTrips->map(function ($trip) {
            $latestLoc = $trip->latestLocation;

            return [
                'trip' => $trip->id,
                'trip_number' => $trip->trip_number,
                'origin' => $trip->origin,
                'destination' => $trip->destination,
                'started_at' => $trip->started_at?->format('Y-m-d H:i:s'),
                'vehicle' => [
                    'id' => $trip->vehicle?->id,
                    'plate_number' => $trip->vehicle?->plate_number,
                    'model' => $trip->vehicle ? "{$trip->vehicle->make} {$trip->vehicle->model}" : null,
                ],
                'driver' => [
                    'id' => $trip->driver?->id,
                    'name' => $trip->driver?->name,
                    'phone' => $trip->driver?->phone,
                ],
                'telemetry' => $latestLoc ? [
                    'latitude' => $latestLoc->latitude,
                    'longitude' => $latestLoc->longitude,
                    'speed_kmh' => $latestLoc->speed_kmh,
                    'last_ping_at' => $latestLoc->recorded_at?->toIso8601String(),
                    'is_stale' => $latestLoc->recorded_at?->diffInMinutes(now()) > 15,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'active_fleet_count' => $activeTrips->count(),
            'data' => $mapData,
        ]);
    }
}
