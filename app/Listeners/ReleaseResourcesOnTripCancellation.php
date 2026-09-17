<?php

namespace App\Listeners;

use App\Enums\DriverStatus;
use App\Enums\VehicleStatus;
use App\Events\TripCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReleaseResourcesOnTripCancellation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TripCancelled $event): void
    {
        $trip = $event->trip;

        $trip->vehicle->update([
            'status' => VehicleStatus::AVAILABLE,
        ]);

        $trip->driver->update([
            'status' => DriverStatus::AVAILABLE,
        ]);
    }
}
