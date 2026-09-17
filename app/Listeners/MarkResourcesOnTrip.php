<?php

namespace App\Listeners;

use App\Events\TripDispatched;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Enums\DriverStatus;
use App\Enums\VehicleStatus;

class MarkResourcesOnTrip
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
    public function handle(TripDispatched $event): void
    {
        $trip = $event->trip;

        $trip->vehicle->update([
            'status' => VehicleStatus::ON_TRIP,
        ]);

        $trip->driver->update([
            'status' => DriverStatus::ON_TRIP,
        ]);
    }
}
