<?php

namespace App\Http\Controllers\Api;

use App\Enums\DriverStatus;
use App\Enums\TripStatus;
use App\Enums\VehicleStatus;
use App\Http\Requests\StoreTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use Psy\Util\Json;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $trips = Trip::with(['vehicle', 'driver'])
            ->when($request->query('status'), fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        return TripResource::collection($trips);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTripRequest $request): JsonResponse
    {
        $trip = DB::transaction(function () use ($request) {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $driver = Driver::findOrFail($request->driver_id);

            $trip = Trip::create([
                'trip_number' => 'TRP-' . strtoupper(Str::random(8)),
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'origin' => $request->origin,
                'destination' => $request->destination,
                'cargo_details' => $request->cargo_details,
                'start_odometer' => $vehicle->odometer,
                'status' => TripStatus::IN_TRANSIT->value ?? 'scheduled',
                'started_at' => now(),
            ]);

            $vehicle->update(['status' => VehicleStatus::ON_TRIP]);
            $driver->update(['status' => DriverStatus::ON_TRIP]);

            return $trip;
        });

        return response()->json([
            'message' => 'Trip dispatched successfully',
            'data' => new TripResource($trip->load(['vehicle', 'driver'])),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip): JsonResponse
    {
        $validated = $request->validate([
            'end_odometer' => ['nullable', 'integer', 'gt:' . $trip->start_odometer],
            'status' => ['nullable', 'string'],
        ]);

        if ($request->filled('end_odometer')) {
            $trip->update([
                'end_odometer' => $request->end_odometer,
                'status' => TripStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $trip->vehicle->update([
                'odometer' => $request->end_odometer,
                'status' => VehicleStatus::AVAILABLE,
            ]);

            $trip->driver->update([
                'status' => DriverStatus::AVAILABLE,
            ]);

            return response()->json([
                'message' => 'Trip updated successfully',
                'data' => new TripResource($trip->load(['vehicle', 'driver'])),
            ]);
        }
    }

    // POST /api/trips/{trip}/cancel
    public function cancel(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->status === \App\Enums\TripStatus::COMPLETED) {
            return response()->json([
                'message' => 'A completed trip cannot be cancelled.',
            ], 422);
        }

        if ($trip->status === \App\Enums\TripStatus::CANCELLED) {
            return response()->json([
                'message' => 'This trip is already cancelled.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($trip, $validated) {
            $trip->update([
                'status' => \App\Enums\TripStatus::CANCELLED,
                'cancellation_reason' => $validated['reason'],
            ]);

            $trip->vehicle->update([
                'status' => \App\Enums\VehicleStatus::AVAILABLE,
            ]);

            $trip->driver->update([
                'status' => \App\Enums\DriverStatus::AVAILABLE,
            ]);
        });

        return response()->json([
            'message' => 'Trip cancelled successfully. Vehicle and driver are now available.',
            'data' => new TripResource($trip->load(['vehicle', 'driver'])),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
