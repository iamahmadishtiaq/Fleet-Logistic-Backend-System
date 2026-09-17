<?php

namespace Database\Factories;

use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $makesAndModels = [
            'Hino' => 'Dutro 300',
            'Isuzu' => 'Forward FVR',
            'Master' => 'Foton 1000',
            'Shehzore' => 'H100',
        ];

        $make = fake()->randomElement(array_keys($makesAndModels));
        $model = $makesAndModels[$make];

        return [
            'plate_number' => strtoupper(fake()->bothify('???-####')),
            'make' => $make,
            'model' => $model,
            'year' => fake()->numberBetween(2018, 2024),
            'type' => fake()->randomElement(['truck', 'van', 'trailer']),
            'odometer' => fake()->numberBetween(10000, 85000),
            'status' => VehicleStatus::AVAILABLE,
            'fitness_expires_at' => fake()->dateTimeBetween('+6 months', '+2 years'),
        ];
    }
}