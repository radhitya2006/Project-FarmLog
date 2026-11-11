<?php

namespace Tests\Unit;

use App\Models\PlantingSeason;
use App\Support\SeasonFinance;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SeasonFinanceTest extends TestCase
{
    #[DataProvider('summaries')]
    public function test_decimal_calculations(string $expenses, string $sales, string $kg, string $ton, string $balance, string $weight, ?string $cost): void
    {
        $season = new PlantingSeason;
        $season->forceFill(['total_expenses' => $expenses, 'total_sales' => $sales, 'total_kg' => $kg, 'total_ton' => $ton]);
        $summary = SeasonFinance::summarize($season);
        $this->assertSame($balance, $summary['balance']);
        $this->assertSame($weight, $summary['weight_kg']);
        $this->assertSame($cost, $summary['cost_per_kg']);
    }

    public static function summaries(): array
    {
        return [
            'mixed units' => ['1500000.00', '2100000.00', '500.00', '1.00', '600000.00', '1500.00', '1000.00'],
            'negative balance' => ['100.00', '0.00', '3.00', '0.00', '-100.00', '3.00', '33.33'],
            'round up' => ['2.00', '2.00', '3.00', '0.00', '0.00', '3.00', '0.67'],
            'half up' => ['1.01', '1.11', '2.00', '0.00', '0.10', '2.00', '0.51'],
            'no harvest' => ['100.00', '0.00', '0.00', '0.00', '-100.00', '0.00', null],
            'no expense' => ['0.00', '1.00', '5.00', '0.00', '1.00', '5.00', null],
            'large decimals' => ['19999999999999.98', '19999999999999.99', '0.01', '0.00', '0.01', '0.01', '1999999999999998.00'],
        ];
    }

    public function test_legacy_season_without_transactions_or_harvests(): void
    {
        $summary = SeasonFinance::summarize(new PlantingSeason);
        $this->assertSame('0.00', $summary['balance']);
        $this->assertSame('0.00', $summary['weight_kg']);
        $this->assertNull($summary['cost_per_kg']);
    }
}
