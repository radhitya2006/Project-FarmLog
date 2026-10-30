<?php

namespace Database\Factories;

use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\PlantingSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Activity> */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'planting_season_id' => PlantingSeason::factory(),
            'name' => fake()->randomElement(['Penyiraman', 'Pemupukan', 'Penyiangan']),
            'scheduled_for' => now()->toDateString(),
            'status' => ActivityStatus::Planned,
            'notes' => null,
        ];
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ActivityStatus::Finished,
        ]);
    }
}
