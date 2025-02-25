<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Motorcycle'],
            ['name' => 'Car'],
            ['name' => 'Pickup Truck'],
        ];

        foreach ($types as $type) {
            VehicleType::create($type);
        }
    }
}
