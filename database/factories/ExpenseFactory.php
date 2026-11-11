<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\PlantingSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Expense> */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'planting_season_id' => PlantingSeason::factory(),
            'spent_on' => now()->toDateString(),
            'name' => 'Pembelian bibit',
            'category' => 'bibit',
            'amount' => '150000.00',
            'notes' => null,
        ];
    }
}
