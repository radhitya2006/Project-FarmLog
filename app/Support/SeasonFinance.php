<?php

namespace App\Support;

use App\Models\PlantingSeason;

class SeasonFinance
{
    /** The season must be loaded with withFinancialTotals() and withHarvestTotals(). */
    public static function summarize(PlantingSeason $season): array
    {
        $expenses = (string) ($season->total_expenses ?? '0.00');
        $sales = (string) ($season->total_sales ?? '0.00');
        $weight = bcadd((string) ($season->total_kg ?? '0'), bcmul((string) ($season->total_ton ?? '0'), '1000', 2), 2);
        $costPerKg = null;

        if (bccomp($expenses, '0', 2) > 0 && bccomp($weight, '0', 2) > 0) {
            // Half-up rounding for positive amounts, compatible with PHP 8.3.
            $costPerKg = bcadd(bcdiv($expenses, $weight, 3), '0.005', 2);
        }

        return [
            'expenses' => $expenses, 'sales' => $sales,
            'balance' => bcsub($sales, $expenses, 2),
            'weight_kg' => $weight, 'cost_per_kg' => $costPerKg,
        ];
    }
}
