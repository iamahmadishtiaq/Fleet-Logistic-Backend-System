<?php

namespace Database\Seeders;

use App\Enums\DriverStatus;
use App\Enums\TripStatus;
use App\Enums\VehicleStatus;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $this->call(RolesAndPermissionsSeeder::class);

        // 1. Default Admin / Dispatcher Account
        User::firstOrCreate(
            ['email' => 'admin@fleet.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('Password123!'),
            ]
        );

        // 2. 10 Vehicles aur 10 Drivers create karein
        $vehicles = Vehicle::factory()->count(10)->create();
        $drivers = Driver::factory()->count(10)->create();

        // 3. Realistic Completed Trips create karein
        for ($i = 0; $i < 6; $i++) {
            $vehicle = $vehicles[$i];
            $driver = $drivers[$i];
            $startOdo = $vehicle->odometer;
            $distance = rand(300, 1200);
            $endOdo = $startOdo + $distance;

            Trip::create([
                'trip_number' => 'TRP-' . strtoupper(Str::random(8)),
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'origin' => 'Lahore Logistics Hub',
                'destination' => 'Karachi Port Qasim',
                'cargo_details' => 'Industrial Textile Goods',
                'start_odometer' => $startOdo,
                'end_odometer' => $endOdo,
                'status' => TripStatus::COMPLETED,
                'started_at' => now()->subDays(rand(3, 10)),
                'completed_at' => now()->subDays(rand(1, 2)),
            ]);

            // Completed trip ke baad gaari ka odometer aage barha diya
            $vehicle->update(['odometer' => $endOdo]);
        }

        // 4. In-Transit Trips (Gaari aur Driver Busy hon)
        for ($i = 6; $i < 8; $i++) {
            $vehicle = $vehicles[$i];
            $driver = $drivers[$i];

            Trip::create([
                'trip_number' => 'TRP-' . strtoupper(Str::random(8)),
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'origin' => 'Faisalabad Industrial Area',
                'destination' => 'Islamabad Distribution Center',
                'cargo_details' => 'FMCG Food Packets',
                'start_odometer' => $vehicle->odometer,
                'end_odometer' => null,
                'status' => TripStatus::IN_TRANSIT,
                'started_at' => now()->subHours(rand(2, 6)),
            ]);

            $vehicle->update(['status' => VehicleStatus::ON_TRIP]);
            $driver->update(['status' => DriverStatus::ON_TRIP]);
        }
    }
}