<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have vehicle types and users
        if (VehicleType::count() === 0) {
            $this->call(VehicleTypeSeeder::class);
        }

        // Create a regular user if none exists
        $user = User::where('is_admin', false)->first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
                'is_admin' => false,
            ]);
        }

        // Get vehicle types
        $motorcycleType = VehicleType::where('name', 'Motorcycle')->first();
        $carType = VehicleType::where('name', 'Car')->first();
        $pickupType = VehicleType::where('name', 'Pickup Truck')->first();

        // Create motorcycles
        $this->createMotorcycles($user, $motorcycleType);

        // Create cars
        $this->createCars($user, $carType);

        // Create pickup trucks
        $this->createPickupTrucks($user, $pickupType);
    }

    private function createMotorcycles($user, $vehicleType)
    {
        $motorcycles = [
            [
                'registration_number' => 'MOT001',
                'manufacturer' => 'Honda',
                'model' => 'CBR 1000RR',
                'engine_capacity' => 1000,
                'seats' => 2,
                'seat_height' => 83.5,
                'is_approved' => true,
                'created_at' => '2025-02-20 10:00:00'
            ],
            [
                'registration_number' => 'MOT002',
                'manufacturer' => 'Yamaha',
                'model' => 'YZF-R6',
                'engine_capacity' => 600,
                'seats' => 2,
                'seat_height' => 85.0,
                'is_approved' => true,
                'created_at' => '2025-02-21 10:00:00'
            ],
            [
                'registration_number' => 'MOT003',
                'manufacturer' => 'Kawasaki',
                'model' => 'Ninja ZX-10R',
                'engine_capacity' => 998,
                'seats' => 2,
                'seat_height' => 84.5,
                'is_approved' => false,
                'created_at' => '2025-02-22 10:00:00'
            ],
            [
                'registration_number' => 'MOT004',
                'manufacturer' => 'Kawasaki',
                'model' => 'Ninja ZX-10R',
                'engine_capacity' => 998,
                'seats' => 2,
                'seat_height' => 84.5,
                'is_approved' => false,
                'created_at' => '2025-02-23 10:00:00'
            ],
            [
                'registration_number' => 'MOT005',
                'manufacturer' => 'Kawasaki',
                'model' => 'Ninja ZX-10R',
                'engine_capacity' => 998,
                'seats' => 2,
                'seat_height' => 84.5,
                'is_approved' => false,
                'created_at' => '2025-02-24 10:00:00'
            ],
            [
                'registration_number' => 'MOT006',
                'manufacturer' => 'Kawasaki',
                'model' => 'Ninja ZX-10R',
                'engine_capacity' => 998,
                'seats' => 2,
                'seat_height' => 84.5,
                'is_approved' => false,
                'created_at' => '2025-02-25 10:00:00'
            ],
            [
                'registration_number' => 'MOT007',
                'manufacturer' => 'Kawasaki',
                'model' => 'Ninja ZX-10R',
                'engine_capacity' => 998,
                'seats' => 2,
                'seat_height' => 84.5,
                'is_approved' => false,
                'created_at' => '2025-02-26 10:00:00'
            ],
            [
                'registration_number' => 'MOT008',
                'manufacturer' => 'Kawasaki',
                'model' => 'Ninja ZX-10R',
                'engine_capacity' => 998,
                'seats' => 2,
                'seat_height' => 84.5,
                'is_approved' => false,
                'created_at' => '2025-02-27 10:00:00'
            ],
            [
                'registration_number' => 'MOT009',
                'manufacturer' => 'Kawasaki',
                'model' => 'Ninja ZX-10R',
                'engine_capacity' => 998,
                'seats' => 2,
                'seat_height' => 84.5,
                'is_approved' => false,
                'created_at' => '2025-02-27 14:00:00'
            ],
        ];

        foreach ($motorcycles as $motorcycle) {
            Vehicle::create(array_merge($motorcycle, [
                'vehicle_type_id' => $vehicleType->id,
                'user_id' => $user->id,
            ]));
        }
    }

    private function createCars($user, $vehicleType)
    {
        $cars = [
            [
                'registration_number' => 'CAR001',
                'manufacturer' => 'Toyota',
                'model' => 'Camry',
                'engine_capacity' => 2500,
                'seats' => 5,
                'cargo_capacity' => 450.5,
                'is_approved' => true,
                'created_at' => '2025-02-20 10:00:00'
            ],
            [
                'registration_number' => 'CAR002',
                'manufacturer' => 'Honda',
                'model' => 'Civic',
                'engine_capacity' => 1800,
                'seats' => 5,
                'cargo_capacity' => 420.0,
                'is_approved' => true,
                'created_at' => '2025-02-21 10:00:00'
            ],
            [
                'registration_number' => 'CAR003',
                'manufacturer' => 'Ford',
                'model' => 'Mustang',
                'engine_capacity' => 5000,
                'seats' => 4,
                'cargo_capacity' => 380.5,
                'is_approved' => false,
                'created_at' => '2025-02-22 10:00:00'
            ],
            [
                'registration_number' => 'CAR004',
                'manufacturer' => 'Chevrolet',
                'model' => 'Camaro',
                'engine_capacity' => 6500,
                'seats' => 4,
                'cargo_capacity' => 350.0,
                'is_approved' => false,
                'created_at' => '2025-02-23 10:00:00'
            ],
            [
                'registration_number' => 'CAR005',
                'manufacturer' => 'Toyota',
                'model' => 'Corolla',
                'engine_capacity' => 1800,
                'seats' => 5,
                'cargo_capacity' => 400.0,
                'is_approved' => true,
                'created_at' => '2025-02-24 10:00:00'
            ],
            [
                'registration_number' => 'CAR006',
                'manufacturer' => 'Toyota',
                'model' => 'Camry',
                'engine_capacity' => 2500,
                'seats' => 5,
                'cargo_capacity' => 450.5,
                'is_approved' => false,
                'created_at' => '2025-02-25 10:00:00'
            ],
            [
                'registration_number' => 'CAR007',
                'manufacturer' => 'Toyota',
                'model' => 'Camry',
                'engine_capacity' => 2500,
                'seats' => 5,
                'cargo_capacity' => 450.5,
                'is_approved' => true,
                'created_at' => '2025-02-26 10:00:00'
            ],
        ];

        foreach ($cars as $car) {
            Vehicle::create(array_merge($car, [
                'vehicle_type_id' => $vehicleType->id,
                'user_id' => $user->id,
            ]));
        }
    }

    private function createPickupTrucks($user, $vehicleType)
    {
        $pickups = [
            [
                'registration_number' => 'PIC001',
                'manufacturer' => 'Ford',
                'model' => 'F-150',
                'engine_capacity' => 3500,
                'seats' => 5,
                'tonnage' => 1.5,
                'is_approved' => true,
                'created_at' => '2025-02-20 10:00:00'
            ],
            [
                'registration_number' => 'PIC002',
                'manufacturer' => 'Toyota',
                'model' => 'Tacoma',
                'engine_capacity' => 3000,
                'seats' => 5,
                'tonnage' => 1.2,
                'is_approved' => true,
                'created_at' => '2025-02-21 10:00:00'
            ],
            [
                'registration_number' => 'PIC003',
                'manufacturer' => 'Chevrolet',
                'model' => 'Silverado',
                'engine_capacity' => 5300,
                'seats' => 6,
                'tonnage' => 2.0,
                'is_approved' => false,
                'created_at' => '2025-02-22 10:00:00'
            ],
            [
                'registration_number' => 'PIC004',
                'manufacturer' => 'Toyota',
                'model' => 'Tacoma',
                'engine_capacity' => 2700,
                'seats' => 5,
                'tonnage' => 1.2,
                'is_approved' => true,
                'created_at' => '2025-02-23 10:00:00'
            ],
            [
                'registration_number' => 'PIC005',
                'manufacturer' => 'Toyota',
                'model' => 'Tacoma',
                'engine_capacity' => 3400,
                'seats' => 5,
                'tonnage' => 1.2,
                'is_approved' => true,
                'created_at' => '2025-02-24 10:00:00'
            ],
            [
                'registration_number' => 'PIC006',
                'manufacturer' => 'Toyota',
                'model' => 'Tacoma',
                'engine_capacity' => 4000,
                'seats' => 5,
                'tonnage' => 1.2,
                'is_approved' => true,
                'created_at' => '2025-02-25 10:00:00'
            ],
            [
                'registration_number' => 'PIC007',
                'manufacturer' => 'Toyota',
                'model' => 'Tacoma',
                'engine_capacity' => 2500,
                'seats' => 5,
                'tonnage' => 1.2,
                'is_approved' => true,
                'created_at' => '2025-02-26 10:00:00'
            ],
            [
                'registration_number' => 'PIC008',
                'manufacturer' => 'Toyota',
                'model' => 'Tacoma',
                'engine_capacity' => 3200,
                'seats' => 5,
                'tonnage' => 1.2,
                'is_approved' => true,
                'created_at' => '2025-02-27 10:00:00'
            ],
            [
                'registration_number' => 'PIC009',
                'manufacturer' => 'Toyota',
                'model' => 'Tacoma',
                'engine_capacity' => 3800,
                'seats' => 5,
                'tonnage' => 1.2,
                'is_approved' => true,
                'created_at' => '2025-02-27 10:00:00'
            ],
        ];

        foreach ($pickups as $pickup) {
            Vehicle::create(array_merge($pickup, [
                'vehicle_type_id' => $vehicleType->id,
                'user_id' => $user->id,
            ]));
        }
    }
}
