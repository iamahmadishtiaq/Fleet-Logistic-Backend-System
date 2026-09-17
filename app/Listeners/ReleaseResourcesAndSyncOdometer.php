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
        $vehicle = $trip->vehicle;

        if ($trip->end_odometer){
            $vehicle->odometer = $trip->end_odometer;
        }

        if ($vehicle->requiresMaintenance(10000)) {
            $vehicle->status = VehicleStatus::MAINTENANCE;
        }else {
            $vehicle->status = VehicleStatus::AVAILABLE;
        }

        $vehicle->save();

        $trip->driver->update([
            'status' => DriverStatus::AVAILABLE,
        ]);
    }
}
