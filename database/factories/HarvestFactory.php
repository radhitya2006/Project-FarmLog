<?php

namespace Database\Factories;

use App\Enums\HarvestUnit;
use App\Models\Harvest;
use App\Models\PlantingSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Harvest> */
class HarvestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'planting_season_id' => PlantingSeason::factory(),
            'harvested_at' => now()->toDateString(),
            'quantity' => fake()->randomFloat(2, 1, 1000),
            'unit' => HarvestUnit::Kilogram,
            'notes' => null,
        ];
    }
}
