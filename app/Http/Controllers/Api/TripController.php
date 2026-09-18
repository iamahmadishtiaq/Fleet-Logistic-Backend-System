<?php

namespace App\Http\Controllers\Api;

use App\Enums\TripStatus;
use App\Events\TripCompleted;
use App\Events\TripDispatched;
use App\Events\TripCancelled;
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
            ->filter($request->only([
                'status',
                'vehicle_id',
                'driver_id',
                'from_date',
                'to_date',
                'search'
            ]))
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
        $validated = $request->validate([
            'end_odometer' => ['nullable', 'integer', 'gt:' . $trip->start_odometer],
            'fuel_rate_per_liter' => ['nullable', 'numeric', 'min:1'],
            'toll_tax' => ['nullable', 'numeric', 'min:0'],
            'misc_expenses' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string'],
        ]);

        if ($request->filled('end_odometer')) {
            DB::transaction(function () use ($validated, $trip){
                $startOdo = (int) $trip->start_odometer;
                $endOdo = (int) $validated['end_odometer'];
                $distanceCovered = $endOdo - $startOdo;

                $fuelAverage = (float) ($trip->vehicle->fuel_average ?: 8.00);
                $fuelRate = (float) ($validated['fuel_rate_per_liter'] ?? 270.00);
                $tollTax = (float) ($validated['toll_tax'] ?? 0);
                $miscExpenses = (float) ($validated['misc_expenses'] ?? 0);

                $fuelConsumedLiters = $distanceCovered > 0 ? ($distanceCovered / $fuelAverage) : 0;
                $fuelCost = round($fuelConsumedLiters * $fuelRate, 2);
                $totalCost = round($fuelCost + $tollTax + $miscExpenses, 2);

                $trip->update([
                    'end_odometer' => $endOdo,
                    'fuel_rate_per_liter' => $fuelRate,
                    'toll_tax' => $tollTax,
                    'misc_expenses' => $miscExpenses,
                    'fuel_cost' => $fuelCost,
                    'total_cost' => $totalCost,
                    'status' => TripStatus::COMPLETED,
                    'completed_at' => now(),
                ]);

                TripCompleted::dispatch($trip);
            });
        }

        // Return if block se bahar taake "none returned" error na aaye
        return response()->json([
            'message' => 'Trip completed and expense calculated successfully.',
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

            TripCancelled::dispatch($trip);
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