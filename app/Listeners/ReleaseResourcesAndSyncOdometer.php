<?php

namespace App\Listeners;

use App\Enums\DriverStatus;
use App\Enums\VehicleStatus;
use App\Events\TripCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReleaseResourcesAndSyncOdometer
{
    /**
     * Create the event listener.
     */
    public function __construct(TripCompleted $event)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TripCompleted $event): void
    {
        $trip = $event->trip;

        $trip->vehicle->update([
            'odometer' => $trip->end_odometer ?? $trip->vehicle_odometer,
            'status' => VehicleStatus::AVAILABLE,
        ]);

        $trip->driver->update([
            'status' => DriverStatus::AVAILABLE,
        ]);
    }
}
