<?php

namespace Database\Factories;

use App\Enums\DriverStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name('male'),
            'phone' => '03' . fake()->numberBetween(10, 49) . fake()->numerify('#######'),
            'cnic' => fake()->numerify('#####-#######-#'),
            'license_number' => strtoupper(fake()->bothify('LHR-PK-#####')),
            'license_expires_at' => fake()->dateTimeBetween('+1 year', '+4 years'),
            'status' => DriverStatus::AVAILABLE,
        ];
    }
}