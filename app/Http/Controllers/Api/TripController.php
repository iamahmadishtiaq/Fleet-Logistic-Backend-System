<?php

namespace App\Http\Controllers\Api;

use App\Enums\TripStatus;
use App\Events\TripCompleted;
use App\Events\TripDispatched;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                'status' => TripStatus::IN_TRANSIT,
                'started_at' => now(),
            ]);

            // Event fire karein: Listener status update karega
            TripDispatched::dispatch($trip);

            return $trip;
        });

        return response()->json([
            'message' => 'Trip dispatched successfully.',
            'data' => new TripResource($trip->load(['vehicle', 'driver'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trip $trip): JsonResponse
    {
        return response()->json([
            'data' => new TripResource($trip->load(['vehicle', 'driver'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trip $trip): JsonResponse
    {
        $request->validate([
            'end_odometer' => ['nullable', 'integer', 'gt:' . $trip->start_odometer],
            'status' => ['nullable', 'string'],
        ]);

        if ($request->filled('end_odometer')) {
            DB::transaction(function () use ($request, $trip) {
                $trip->update([
                    'end_odometer' => $request->end_odometer,
                    'status' => TripStatus::COMPLETED,
                    'completed_at' => now(),
                ]);

                // Event fire karein: Listener odometer aur statuses sync karega
                TripCompleted::dispatch($trip);
            });
        }

        // Return if block se bahar taake "none returned" error na aaye
        return response()->json([
            'message' => 'Trip updated successfully.',
            'data' => new TripResource($trip->load(['vehicle', 'driver'])),
        ]);
    }

    /**
     * Cancel an active trip.
     */
    public function cancel(Request $request, Trip $trip): JsonResponse
    {
        if ($trip->status === TripStatus::COMPLETED) {
            return response()->json([
                'message' => 'A completed trip cannot be cancelled.',
            ], 422);
        }

        if ($trip->status === TripStatus::CANCELLED) {
            return response()->json([
                'message' => 'This trip is already cancelled.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($trip, $validated) {
            $trip->update([
                'status' => TripStatus::CANCELLED,
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
    public function destroy(Trip $trip): JsonResponse
    {
        $trip->delete();

        return response()->json([
            'message' => 'Trip deleted successfully.',
        ]);
    }
}