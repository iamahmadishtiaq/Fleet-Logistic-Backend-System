<?php

namespace Tests\Feature;

use App\Enums\DriverStatus;
use App\Enums\TripStatus;
use App\Enums\VehicleStatus;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Vehicle $vehicle;
    protected Driver $driver;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Authenticated User
        $this->user = User::factory()->create();

        // 2. Test Vehicle aur Driver
        $this->vehicle = Vehicle::factory()->create([
            'odometer' => 10000,
            'last_service_odometer' => 10000, // <-- Yeh line add karein
            'fuel_average' => 8.00,
            'status' => VehicleStatus::AVAILABLE,
        ]);

        $this->driver = Driver::factory()->create([
            'status' => DriverStatus::AVAILABLE,
        ]);
    }

    public function test_can_dispatch_trip_and_update_resource_statuses(): void
    {
        $payload = [
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'origin' => 'Lahore Hub',
            'destination' => 'Islamabad Station',
            'cargo_details' => 'Electronic Goods',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/trips', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'in_transit');

        // Database assertions
        $this->assertDatabaseHas('trips', [
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'status' => TripStatus::IN_TRANSIT->value,
            'start_odometer' => 10000,
        ]);

        // Verify listener updated vehicle and driver statuses
        $this->assertEquals(VehicleStatus::ON_TRIP, $this->vehicle->fresh()->status);
        $this->assertEquals(DriverStatus::ON_TRIP, $this->driver->fresh()->status);
    }

    public function test_can_complete_trip_and_calculate_costs(): void
    {
        // Active trip setup karein
        $trip = Trip::create([
            'trip_number' => 'TRP-TEST1234',
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'origin' => 'Lahore',
            'destination' => 'Rawalpindi',
            'start_odometer' => 10000,
            'status' => TripStatus::IN_TRANSIT,
            'started_at' => now(),
        ]);

        $payload = [
            'end_odometer' => 10400, // Distance = 400 KM
            'fuel_rate_per_liter' => 270.00,
            'toll_tax' => 1200.00,
            'misc_expenses' => 300.00,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/trips/{$trip->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        // 400 KM / 8.00 km/l = 50 Liters
        // 50 Liters * 270 = 13,500 Fuel Cost
        // Total = 13,500 + 1200 + 300 = 15,000 Total Cost
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => TripStatus::COMPLETED->value,
            'fuel_cost' => 13500.00,
            'total_cost' => 15000.00,
        ]);

        // Vehicle odometer sync verification
        $this->assertEquals(10400, $this->vehicle->fresh()->odometer);
        $this->assertEquals(VehicleStatus::AVAILABLE, $this->vehicle->fresh()->status);
        $this->assertEquals(DriverStatus::AVAILABLE, $this->driver->fresh()->status);
    }

    public function test_can_cancel_active_trip_and_free_resources(): void
    {
        $trip = Trip::create([
            'trip_number' => 'TRP-CANCEL01',
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $this->driver->id,
            'origin' => 'Lahore',
            'destination' => 'Multan',
            'start_odometer' => 10000,
            'status' => TripStatus::IN_TRANSIT,
            'started_at' => now(),
        ]);

        // Set initial busy status
        $this->vehicle->update(['status' => VehicleStatus::ON_TRIP]);
        $this->driver->update(['status' => DriverStatus::ON_TRIP]);

        $payload = [
            'reason' => 'Road blocked due to land sliding.',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/trips/{$trip->id}/cancel", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'status' => TripStatus::CANCELLED->value,
            'cancellation_reason' => 'Road blocked due to land sliding.',
        ]);

        // Verify resources are released
        $this->assertEquals(VehicleStatus::AVAILABLE, $this->vehicle->fresh()->status);
        $this->assertEquals(DriverStatus::AVAILABLE, $this->driver->fresh()->status);
    }
}
