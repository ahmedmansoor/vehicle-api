<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleTypeFactory extends Factory
{
    protected $model = VehicleType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // By default, return one of the three required types randomly
        return [
            'name' => $this->faker->randomElement(['Motorcycle', 'Car', 'Pickup Truck']),
        ];
    }

    /**
     * Configure the model factory to create a Motorcycle type.
     */
    public function motorcycle(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Motorcycle',
        ]);
    }

    /**
     * Configure the model factory to create a Car type.
     */
    public function car(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Car',
        ]);
    }

    /**
     * Configure the model factory to create a Pickup Truck type.
     */
    public function pickupTruck(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pickup Truck',
        ]);
    }
}
