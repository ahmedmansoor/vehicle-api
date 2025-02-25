<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'manufacturer' => $this->faker->company(),
            'model' => $this->faker->word(),
            'engine_capacity' => $this->faker->randomFloat(2, 500, 5000),
            'seats' => $this->faker->numberBetween(1, 8),
            'seat_height' => $this->faker->optional()->randomFloat(2, 60, 100),
            'cargo_capacity' => $this->faker->optional()->randomFloat(2, 100, 1000),
            'tonnage' => $this->faker->optional()->randomFloat(2, 0.5, 5),
            'is_approved' => $this->faker->boolean(),
            'vehicle_type_id' => VehicleType::factory(),
            'user_id' => User::factory(),
        ];
    }
}
