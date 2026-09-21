<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\TripStatus;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;

class TripLocationController extends Controller
{
    // Store incoming GPS ping for an active trip.
    public function store(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->status !== TripStatus::IN_TRANSIT) {
            return response()->json([
                'message' => 'GPS corrdinates can only be recorded for trips currently in transit.'
            ], 422);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:250'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $location = $trip->locations()->create([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed_kmh' => $validated['speed_kmh'] ?? 0,
            'recorded_at' => $validated['recorded_at'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Location ping recorded successfully.',
            'data' => $location,
        ], 201);
    }

    public function history(Trip $trip): JsonResponse
    {
        $trail = $trip->locations()
            ->orderBy('recorded_at', 'asc')
            ->limit(500)
            ->get();

        return response()->json([
            'trip_number' => $trip->trip_number,
            'status' => $trip->status,
            'latest_location' => $trail->last(),
            'trail' => $trail,
        ]);
    }
}
