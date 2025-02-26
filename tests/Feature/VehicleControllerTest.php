<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var User */
    protected $user;

    /** @var User */
    protected $admin;

    /** @var VehicleType */
    protected $carType;

    /** @var VehicleType */
    protected $motorcycleType;

    /** @var VehicleType */
    protected $pickupType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user and admin
        $this->user = User::factory()->create(['is_admin' => false]);
        $this->admin = User::factory()->create(['is_admin' => true]);

        // Create vehicle types
        $this->motorcycleType = VehicleType::create(['name' => 'Motorcycle']);
        $this->carType = VehicleType::create(['name' => 'Car']);
        $this->pickupType = VehicleType::create(['name' => 'Pickup Truck']);
    }

    public function test_it_can_list_approved_vehicles()
    {
        // Create some approved and unapproved vehicles
        $approvedVehicle = Vehicle::factory()->create([
            'is_approved' => true,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        $unapprovedVehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Test public endpoint should only show approved vehicles
        $response = $this->getJson('/api/v1/vehicles');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $approvedVehicle->id])
            ->assertJsonMissing(['id' => $unapprovedVehicle->id]);
    }

    public function test_it_can_filter_vehicles_by_type()
    {
        // Create vehicles of different types
        $car = Vehicle::factory()->create([
            'is_approved' => true,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        $motorcycle = Vehicle::factory()->create([
            'is_approved' => true,
            'vehicle_type_id' => $this->motorcycleType->id,
            'user_id' => $this->user->id
        ]);

        // Test filtering
        $response = $this->getJson('/api/v1/vehicles?vehicle_type_id=' . $this->carType->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $car->id])
            ->assertJsonMissing(['id' => $motorcycle->id]);
    }

    public function test_it_can_search_vehicles()
    {
        // Create vehicles with different manufacturers
        $toyota = Vehicle::factory()->create([
            'is_approved' => true,
            'manufacturer' => 'Toyota',
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        $honda = Vehicle::factory()->create([
            'is_approved' => true,
            'manufacturer' => 'Honda',
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Test search
        $response = $this->getJson('/api/v1/vehicles?search=toyota');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $toyota->id])
            ->assertJsonMissing(['id' => $honda->id]);
    }

    public function test_it_can_show_vehicle_details_if_approved()
    {
        // Create an approved vehicle
        $vehicle = Vehicle::factory()->create([
            'is_approved' => true,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Test public endpoint
        $response = $this->getJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $vehicle->id]);
    }

    public function test_it_cannot_show_unapproved_vehicle_details_to_public()
    {
        // Create an unapproved vehicle
        $vehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Test public endpoint
        $response = $this->getJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertStatus(404);
    }

    public function test_owner_can_see_their_unapproved_vehicle()
    {
        // Create an unapproved vehicle
        $vehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Test as owner
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $vehicle->id]);
    }

    public function test_admin_can_see_any_unapproved_vehicle()
    {
        // Create an unapproved vehicle
        $vehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Test as admin
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $vehicle->id]);
    }

    public function test_authenticated_user_can_create_car()
    {
        $vehicleData = [
            'registration_number' => 'ABC123456',
            'manufacturer' => 'Toyota',
            'model' => 'Camry',
            'engine_capacity' => 2.5,
            'seats' => 5,
            'vehicle_type_id' => $this->carType->id,
            'cargo_capacity' => 500
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vehicles', $vehicleData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Vehicle created successfully',
                'registration_number' => 'ABC123456',
                'is_approved' => false
            ]);

        $this->assertDatabaseHas('vehicles', [
            'registration_number' => 'ABC123456',
            'user_id' => $this->user->id
        ]);
    }

    public function test_authenticated_user_can_create_motorcycle()
    {
        $vehicleData = [
            'registration_number' => 'MOTO123',
            'manufacturer' => 'Honda',
            'model' => 'CBR',
            'engine_capacity' => 1.0,
            'seats' => 2,
            'vehicle_type_id' => $this->motorcycleType->id,
            'seat_height' => 85.5
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vehicles', $vehicleData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Vehicle created successfully',
                'registration_number' => 'MOTO123',
                'is_approved' => false
            ]);

        $this->assertDatabaseHas('vehicles', [
            'registration_number' => 'MOTO123',
            'seat_height' => 85.5
        ]);
    }

    public function test_authenticated_user_can_create_pickup_truck()
    {
        $vehicleData = [
            'registration_number' => 'TRUCK123',
            'manufacturer' => 'Ford',
            'model' => 'F-150',
            'engine_capacity' => 5.0,
            'seats' => 5,
            'vehicle_type_id' => $this->pickupType->id,
            'tonnage' => 2.5
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vehicles', $vehicleData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Vehicle created successfully',
                'registration_number' => 'TRUCK123',
                'is_approved' => false
            ]);

        $this->assertDatabaseHas('vehicles', [
            'registration_number' => 'TRUCK123',
            'tonnage' => 2.5
        ]);
    }

    public function test_it_validates_required_fields_when_creating_vehicle()
    {
        // Missing required fields
        $incompleteData = [
            'manufacturer' => 'Toyota',
            'model' => 'Camry'
            // Missing other required fields
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vehicles', $incompleteData);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Validation failed'])
            ->assertJsonValidationErrors(['registration_number', 'engine_capacity', 'seats']);
    }

    public function test_it_validates_type_specific_fields_when_creating_vehicle()
    {
        // Missing car-specific field
        $carData = [
            'registration_number' => 'CAR12345',
            'manufacturer' => 'Toyota',
            'model' => 'Camry',
            'engine_capacity' => 2.5,
            'seats' => 5,
            'vehicle_type_id' => $this->carType->id
            // Missing cargo_capacity
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/vehicles', $carData);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Vehicle type-specific validation failed'])
            ->assertJsonValidationErrors(['cargo_capacity']);
    }

    public function test_owner_can_update_their_vehicle()
    {
        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'is_approved' => true,
            'registration_number' => 'OLD12345',
            'manufacturer' => 'Old Manufacturer',
            'model' => 'Old Model',
            'vehicle_type_id' => $this->carType->id,
            'cargo_capacity' => 400,
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'registration_number' => 'NEW12345',
            'manufacturer' => 'New Manufacturer',
            'model' => 'New Model',
            'engine_capacity' => 3.0,
            'seats' => 5,
            'vehicle_type_id' => $this->carType->id,
            'cargo_capacity' => 500
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/vehicles/' . $vehicle->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'registration_number' => 'NEW12345',
                'manufacturer' => 'New Manufacturer',
                'model' => 'New Model',
                'is_approved' => false // Should reset approval
            ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'registration_number' => 'NEW12345',
            'is_approved' => false
        ]);
    }

    public function test_admin_can_update_any_vehicle()
    {
        // Create a vehicle owned by regular user
        $vehicle = Vehicle::factory()->create([
            'registration_number' => 'USER1234',
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'registration_number' => 'ADMIN123',
            'manufacturer' => 'Admin Updated',
            'model' => 'Updated Model',
            'engine_capacity' => 3.0,
            'seats' => 5,
            'vehicle_type_id' => $this->carType->id,
            'cargo_capacity' => 500
        ];

        $response = $this->actingAs($this->admin)
            ->putJson('/api/v1/vehicles/' . $vehicle->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'registration_number' => 'ADMIN123',
                'manufacturer' => 'Admin Updated'
            ]);
    }

    public function test_non_owner_cannot_update_vehicle()
    {
        // Create a second non-admin user
        $anotherUser = User::factory()->create(['is_admin' => false]);

        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'registration_number' => 'OWNER123',
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'registration_number' => 'HACKED123',
            'manufacturer' => 'Hacked',
            'model' => 'Hacked Model',
            'engine_capacity' => 3.0,
            'seats' => 5,
            'vehicle_type_id' => $this->carType->id,
            'cargo_capacity' => 500
        ];

        // Try to update as non-owner
        $response = $this->actingAs($anotherUser)
            ->putJson('/api/v1/vehicles/' . $vehicle->id, $updateData);

        $response->assertStatus(403);

        // Verify data was not changed
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'registration_number' => 'OWNER123'
        ]);
    }

    public function test_owner_can_change_vehicle_type()
    {
        // Create a car
        $vehicle = Vehicle::factory()->create([
            'registration_number' => 'CAR12345',
            'vehicle_type_id' => $this->carType->id,
            'cargo_capacity' => 500,
            'seat_height' => null,
            'tonnage' => null,
            'user_id' => $this->user->id
        ]);

        // Change to motorcycle
        $updateData = [
            'registration_number' => 'CAR12345',
            'manufacturer' => 'Honda',
            'model' => 'CBR',
            'engine_capacity' => 1.0,
            'seats' => 2,
            'vehicle_type_id' => $this->motorcycleType->id,
            'seat_height' => 85.5
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/vehicles/' . $vehicle->id, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'vehicle_type_id' => $this->motorcycleType->id,
            'seat_height' => 85.5,
            'cargo_capacity' => null,
            'tonnage' => null
        ]);
    }

    public function test_admin_can_approve_vehicle()
    {
        // Create an unapproved vehicle
        $vehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Approve as admin
        $response = $this->actingAs($this->admin)
            ->patchJson('/api/v1/vehicles/' . $vehicle->id . '/approve');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Vehicle approved successfully',
                'is_approved' => true
            ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'is_approved' => true
        ]);
    }

    public function test_regular_user_cannot_approve_vehicle()
    {
        // Create an unapproved vehicle
        $vehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Try to approve as regular user
        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/vehicles/' . $vehicle->id . '/approve');

        $response->assertStatus(403);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'is_approved' => false
        ]);
    }

    public function test_user_can_view_their_unapproved_vehicles()
    {
        // Create approved and unapproved vehicles for the user
        $approvedVehicle = Vehicle::factory()->create([
            'is_approved' => true,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        $unapprovedVehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Create another user's unapproved vehicle
        $anotherUser = User::factory()->create();
        $otherUserVehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $anotherUser->id
        ]);

        // Test user can only see their unapproved vehicles
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/vehicles/unapproved');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $unapprovedVehicle->id])
            ->assertJsonMissing(['id' => $approvedVehicle->id])
            ->assertJsonMissing(['id' => $otherUserVehicle->id]);
    }

    public function test_admin_can_view_all_unapproved_vehicles()
    {
        // Create unapproved vehicles for different users
        $userVehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        $anotherUser = User::factory()->create();
        $otherUserVehicle = Vehicle::factory()->create([
            'is_approved' => false,
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $anotherUser->id
        ]);

        // Test admin can see all unapproved vehicles
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/vehicles/unapproved');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $userVehicle->id])
            ->assertJsonFragment(['id' => $otherUserVehicle->id]);
    }

    public function test_owner_can_delete_their_vehicle()
    {
        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Delete as owner
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Vehicle deleted successfully']);

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id
        ]);
    }

    public function test_admin_can_delete_any_vehicle()
    {
        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Delete as admin
        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Vehicle deleted successfully']);

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id
        ]);
    }

    public function test_non_owner_cannot_delete_vehicle()
    {
        // Create a second non-admin user
        $anotherUser = User::factory()->create(['is_admin' => false]);

        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->carType->id,
            'user_id' => $this->user->id
        ]);

        // Try to delete as non-owner
        $response = $this->actingAs($anotherUser)
            ->deleteJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertStatus(403);

        // Verify vehicle was not deleted
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id
        ]);
    }
}
