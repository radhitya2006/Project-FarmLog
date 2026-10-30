<?php

namespace Database\Factories;

use App\Enums\PlantingSeasonStatus;
use App\Models\Land;
use App\Models\PlantingSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlantingSeason> */
class PlantingSeasonFactory extends Factory
{
    public function definition(): array
    {
        $plantedAt = fake()->dateTimeBetween('-6 months', '-3 months');

        return [
            'land_id' => Land::factory(),
            'crop_name' => fake()->randomElement(['Cabai', 'Padi', 'Tomat']),
            'planted_at' => $plantedAt,
            'expected_harvest_at' => (clone $plantedAt)->modify('+120 days'),
            'status' => PlantingSeasonStatus::Active,
            'notes' => null,
        ];
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PlantingSeasonStatus::Finished,
        ]);
    }
}
