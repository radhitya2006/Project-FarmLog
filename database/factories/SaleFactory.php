<?php

namespace Database\Factories;

use App\Models\PlantingSeason;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sale> */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'planting_season_id' => PlantingSeason::factory(),
            'received_on' => now()->toDateString(),
            'name' => 'Penjualan panen',
            'amount' => '150000.00',
            'notes' => null,
        ];
    }
}
