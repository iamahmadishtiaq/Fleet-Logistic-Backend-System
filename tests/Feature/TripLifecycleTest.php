<?php

namespace Tests\Feature;

use App\Enums\TripStatus;
use App\Enums\VehicleStatus;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TripLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $dispatcher;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles aur permissions seed karein
        $this->seed(RolesAndPermissionsSeeder::class);

        // 2. Dispatcher user setup
        $this->dispatcher = User::factory()->create([
            'email' => 'dispatcher@fleet.test',
        ]);
        $this->dispatcher->assignRole('dispatcher');

        // 3. Admin user setup
        $this->admin = User::factory()->create([
            'email' => 'admin@fleet.test',
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_dispatcher_can_create_a_trip_and_vehicle_is_marked_on_trip(): void
    {
        Sanctum::actingAs($this->dispatcher);

        $vehicle = Vehicle::factory()->create([
            'status' => VehicleStatus::AVAILABLE,
            'odometer' => 15000,
        ]);

        $driver = Driver::factory()->create([
            'status' => 'available',
        ]);

        $payload = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Lahore Hub',
            'destination' => 'Islamabad Depot',
            'cargo_details' => 'Electronics Consignment',
        ];

        $response = $this->postJson('/api/trips', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('trips', [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'status' => TripStatus::IN_TRANSIT,
        ]);

        // Verify vehicle status changed to on_trip
        $this->assertEquals(VehicleStatus::ON_TRIP, $vehicle->fresh()->status);
    }

    public function test_cannot_assign_vehicle_under_maintenance_to_a_new_trip(): void
    {
        Sanctum::actingAs($this->dispatcher);

        $vehicle = Vehicle::factory()->create([
            'status' => VehicleStatus::MAINTENANCE,
        ]);

        $driver = Driver::factory()->create([
            'status' => 'available',
        ]);

        $payload = [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'origin' => 'Lahore Hub',
            'destination' => 'Multan Depot',
        ];

        $response = $this->postJson('/api/trips', $payload);

        $response->assertStatus(422);
    }

    public function test_dispatcher_cannot_delete_a_vehicle(): void
    {
        Sanctum::actingAs($this->dispatcher);

        $vehicle = Vehicle::factory()->create();

        $response = $this->deleteJson("/api/vehicles/{$vehicle->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_a_vehicle(): void
    {
        Sanctum::actingAs($this->admin);

        $vehicle = Vehicle::factory()->create();

        $response = $this->deleteJson("/api/vehicles/{$vehicle->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }
}