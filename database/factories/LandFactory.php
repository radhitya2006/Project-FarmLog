<?php

namespace Database\Factories;

use App\Models\Land;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Land> */
class LandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Lahan '.fake()->city(),
            'area_hectares' => fake()->randomFloat(2, 0.1, 10),
            'location' => fake()->city(),
            'notes' => null,
        ];
    }
}
