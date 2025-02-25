<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $vehicleType;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed vehicle types
        $this->seed(\Database\Seeders\VehicleTypeSeeder::class);

        // Create a regular user
        $this->user = User::factory()->create();

        // Create an admin user
        $this->admin = User::factory()->create(['is_admin' => true]);

        // Get the first vehicle type (Motorcycle)
        $this->vehicleType = VehicleType::first();
    }

    public function test_user_can_create_vehicle()
    {
        $vehicleData = [
            'registration_number' => 'ABC123',
            'manufacturer' => 'Honda',
            'model' => 'CBR',
            'engine_capacity' => 1000,
            'seats' => 2,
            'seat_height' => 80.5,
            'vehicle_type_id' => $this->vehicleType->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/vehicles', $vehicleData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'registration_number' => 'ABC123',
                'is_approved' => false,
            ]);

        $this->assertDatabaseHas('vehicles', [
            'registration_number' => 'ABC123',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_update_own_vehicle()
    {
        $vehicle = Vehicle::factory()->create([
            'user_id' => $this->user->id,
            'vehicle_type_id' => $this->vehicleType->id,
            'registration_number' => 'ABC123',
        ]);

        $updateData = [
            'registration_number' => 'XYZ789',
            'manufacturer' => 'Updated Manufacturer',
            'model' => 'Updated Model',
            'engine_capacity' => 1200,
            'seats' => 2,
            'seat_height' => 85.5,
            'vehicle_type_id' => $this->vehicleType->id,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/vehicles/{$vehicle->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'registration_number' => 'XYZ789',
                'manufacturer' => 'Updated Manufacturer',
            ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'registration_number' => 'XYZ789',
        ]);
    }

    public function test_user_cannot_update_others_vehicle()
    {
        $otherUser = User::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $otherUser->id,
            'vehicle_type_id' => $this->vehicleType->id,
        ]);

        $updateData = [
            'registration_number' => 'XYZ789',
            'manufacturer' => 'Updated Manufacturer',
            'model' => 'Updated Model',
            'engine_capacity' => 1200,
            'seats' => 2,
            'seat_height' => 85.5,
            'vehicle_type_id' => $this->vehicleType->id,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/vehicles/{$vehicle->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_vehicle()
    {
        $vehicle = Vehicle::factory()->create([
            'user_id' => $this->user->id,
            'vehicle_type_id' => $this->vehicleType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/vehicles/{$vehicle->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    public function test_user_cannot_delete_others_vehicle()
    {
        $otherUser = User::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $otherUser->id,
            'vehicle_type_id' => $this->vehicleType->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/vehicles/{$vehicle->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_admin_can_approve_vehicle()
    {
        $vehicle = Vehicle::factory()->create([
            'user_id' => $this->user->id,
            'vehicle_type_id' => $this->vehicleType->id,
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/vehicles/{$vehicle->id}/approve");

        $response->assertStatus(200)
            ->assertJsonFragment(['is_approved' => true]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'is_approved' => true,
        ]);
    }
}
