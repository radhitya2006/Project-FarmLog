<?php

namespace Tests\Feature\Farmer;

use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\Expense;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function transactionRoute(string $kind, string $action, PlantingSeason $season, Expense|Sale|null $record = null): string
    {
        return route('farmer.lands.planting-seasons.'.$kind.'.'.$action, [$season->land, $season, ...($record ? [$record] : [])]);
    }

    private function data(string $kind, array $overrides = []): array
    {
        return array_replace([
            'name' => 'Transaksi musim', 'amount' => '250000.25', 'notes' => 'Sudah dibayar.',
            ...($kind === 'expenses' ? ['spent_on' => '2026-01-01', 'category' => 'bibit'] : ['received_on' => '2026-01-01']),
        ], $overrides);
    }

    public function test_farmer_can_manage_both_transaction_types_in_active_and_finished_seasons(): void
    {
        foreach (PlantingSeasonStatus::cases() as $status) {
            $season = PlantingSeason::factory()->create(['status' => $status, 'planted_at' => '2026-02-01']);
            $this->actingAs($season->land->user);
            foreach (['expenses', 'sales'] as $kind) {
                $this->get($this->transactionRoute($kind, 'create', $season))->assertOk()->assertSee('Nominal (Rp)');
                $this->post($this->transactionRoute($kind, 'store', $season), $this->data($kind))
                    ->assertSessionHasNoErrors()->assertRedirect(route('farmer.finance.show', [$season->land, $season]));
                $record = $season->{$kind}()->sole();
                $this->assertSame('250000.25', $record->amount);
                $this->get($this->transactionRoute($kind, 'edit', $season, $record))->assertOk()->assertSee('250000.25');
                $this->put($this->transactionRoute($kind, 'update', $season, $record), $this->data($kind, ['amount' => '500000.50']))
                    ->assertSessionHasNoErrors()->assertRedirect();
                $this->assertSame('500000.50', $record->fresh()->amount);
                $this->get(route('farmer.finance.show', [$season->land, $season]))->assertOk()->assertSee('Rp500.000,50');
                $this->delete($this->transactionRoute($kind, 'destroy', $season, $record))->assertSessionHasErrors('confirmed');
                $this->assertModelExists($record);
                $this->delete($this->transactionRoute($kind, 'destroy', $season, $record), ['confirmed' => '0'])->assertSessionHasErrors('confirmed');
                $this->delete($this->transactionRoute($kind, 'destroy', $season, $record), ['confirmed' => '1'])->assertRedirect();
                $this->assertModelMissing($record);
                $this->get(route('farmer.finance.show', [$season->land, $season]))->assertOk()
                    ->assertViewHas('summary', fn ($summary) => $summary[$kind] === '0.00');
            }
        }
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_data_is_rejected_for_create_and_update(string $field, mixed $value): void
    {
        $season = PlantingSeason::factory()->create();
        $this->actingAs($season->land->user);
        foreach (['expenses' => Expense::class, 'sales' => Sale::class] as $kind => $model) {
            $record = $model::factory()->for($season)->create();
            $actualField = $field === 'date' ? ($kind === 'expenses' ? 'spent_on' : 'received_on') : $field;
            $data = $this->data($kind, [$actualField => $value]);
            $this->post($this->transactionRoute($kind, 'store', $season), $data)->assertSessionHasErrors($actualField);
            $this->put($this->transactionRoute($kind, 'update', $season, $record), $data)->assertSessionHasErrors($actualField);
            $this->assertSame('150000.00', $record->fresh()->amount);
            $this->assertDatabaseCount($kind, 1);
        }
    }

    public static function invalidFields(): array
    {
        return [
            ['name', ''], ['name', str_repeat('a', 151)], ['name', ['array']],
            ['date', '2026-02-30'], ['date', ''], ['date', '01/01/2026'],
            ['amount', ''], ['amount', '0'], ['amount', '-1'], ['amount', '1.001'],
            ['amount', '10000000000000'], ['amount', '1e3'], ['amount', ['1']],
            ['notes', str_repeat('x', 5001)], ['notes', ['invalid']],
            ['user_id', 1], ['land_id', null], ['planting_season_id', 1],
        ];
    }

    public function test_categories_boundaries_validation_feedback_and_escaped_notes(): void
    {
        $season = PlantingSeason::factory()->create();
        $this->actingAs($season->land->user);
        foreach (['', 'unknown', ['bibit']] as $category) {
            $this->post($this->transactionRoute('expenses', 'store', $season), $this->data('expenses', ['category' => $category]))
                ->assertSessionHasErrors('category');
        }
        foreach (['expenses', 'sales'] as $kind) {
            foreach (['0.01', '9999999999999.99', '1'] as $amount) {
                $this->post($this->transactionRoute($kind, 'store', $season), $this->data($kind, ['amount' => $amount, 'notes' => null]))
                    ->assertSessionHasNoErrors();
            }
        }
        $this->post($this->transactionRoute('expenses', 'store', $season), $this->data('expenses', ['notes' => '<script>alert(1)</script>']))->assertSessionHasNoErrors();
        $this->get(route('farmer.finance.show', [$season->land, $season]))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->from($this->transactionRoute('expenses', 'create', $season))->post($this->transactionRoute('expenses', 'store', $season), $this->data('expenses', ['amount' => '0']))
            ->assertSessionHasInput('name', 'Transaksi musim');
        $this->withCookie(config('session.cookie'), session()->getId())->get($this->transactionRoute('expenses', 'create', $season))
            ->assertOk()->assertSee('id="amount-error"', false)->assertSee('value="Transaksi musim"', false);
    }

    public function test_totals_cover_all_pages_and_owner_seasons_without_multiplying_joins(): void
    {
        $season = PlantingSeason::factory()->create();
        Expense::factory()->count(12)->for($season)->create(['amount' => '100.10']);
        Sale::factory()->count(11)->for($season)->create(['amount' => '200.20']);
        Harvest::factory()->for($season)->create(['quantity' => '500.00', 'unit' => 'kg']);
        Harvest::factory()->for($season)->create(['quantity' => '1.00', 'unit' => 'ton']);
        $otherSeason = PlantingSeason::factory()->for($season->land)->create(['status' => PlantingSeasonStatus::Finished]);
        Expense::factory()->for($otherSeason)->create(['amount' => '50.50']);
        Sale::factory()->create(['amount' => '999999.99']);
        $url = route('farmer.finance.show', [$season->land, $season]);
        $this->actingAs($season->land->user)->get($url)->assertOk()
            ->assertViewHas('summary', fn ($s) => $s['expenses'] === '1201.20' && $s['sales'] === '2202.20' && $s['balance'] === '1001.00' && $s['cost_per_kg'] === '0.80')
            ->assertViewHas('expenses', fn ($p) => $p->total() === 12 && $p->count() === 10)
            ->assertViewHas('sales', fn ($p) => $p->total() === 11 && $p->count() === 10);
        $this->get($url.'?expenses_page=2&sales_page=2')->assertOk()
            ->assertViewHas('expenses', fn ($p) => $p->count() === 2 && str_contains($p->previousPageUrl(), 'sales_page=2'))
            ->assertViewHas('sales', fn ($p) => $p->count() === 1 && str_contains($p->previousPageUrl(), 'expenses_page=2'))
            ->assertSee('Rp1.201,20')->assertSee('Rp2.202,20');
        $this->get(route('farmer.finance.index'))->assertOk()
            ->assertViewHas('seasons', fn ($p) => $p->total() === 2)
            ->assertViewHas('summary', fn ($s) => $s['expenses'] === '1251.70' && $s['sales'] === '2202.20' && $s['balance'] === '950.50');
    }

    public function test_large_totals_are_rendered_without_float_precision_loss(): void
    {
        $season = PlantingSeason::factory()->create();
        Expense::factory()->count(2)->for($season)->create(['amount' => '9999999999999.99']);
        Harvest::factory()->for($season)->create(['quantity' => '0.01', 'unit' => 'kg']);
        $this->actingAs($season->land->user)->get(route('farmer.finance.show', [$season->land, $season]))->assertOk()
            ->assertSee('Rp19.999.999.999.999,98')->assertSee('−Rp19.999.999.999.999,98')
            ->assertSee('Rp1.999.999.999.999.998,00');
    }

    public function test_empty_states_navigation_and_index_pagination(): void
    {
        $farmer = User::factory()->create();
        $this->actingAs($farmer)->get(route('farmer.finance.index'))->assertOk()->assertSee('Belum ada musim tanam')->assertSee('Rp0,00');
        $land = Land::factory()->for($farmer)->create();
        $seasons = PlantingSeason::factory()->count(13)->for($land)->create();
        $this->get(route('farmer.finance.index'))->assertOk()->assertViewHas('seasons', fn ($p) => $p->total() === 13 && $p->count() === 12);
        $this->get(route('farmer.finance.index', ['page' => 2]))->assertOk()->assertViewHas('seasons', fn ($p) => $p->count() === 1);
        $this->get(route('farmer.finance.index', ['page' => 99]))->assertOk()->assertSee('Halaman pertama');
        $this->get(route('farmer.finance.show', [$land, $seasons->first()]))->assertOk()
            ->assertSee('Belum ada pengeluaran')->assertSee('Belum ada penjualan')->assertSee('Belum ada berat panen tercatat.')
            ->assertSee('href="'.route('farmer.finance.index').'" class="is-active"', false);
        $this->get($this->transactionRoute('expenses', 'create', $seasons->first()))->assertOk()
            ->assertSee('href="'.route('farmer.finance.index').'" class="is-active"', false)
            ->assertDontSee('href="'.route('farmer.lands.index').'" class="is-active"', false);
    }

    public function test_foreign_owner_cannot_read_or_mutate_finances(): void
    {
        $season = PlantingSeason::factory()->create();
        $this->actingAs(User::factory()->create())->get(route('farmer.finance.show', [$season->land, $season]))->assertForbidden();
        foreach (['expenses' => Expense::class, 'sales' => Sale::class] as $kind => $model) {
            $record = $model::factory()->for($season)->create();
            $this->get($this->transactionRoute($kind, 'create', $season))->assertForbidden();
            $this->get($this->transactionRoute($kind, 'edit', $season, $record))->assertForbidden();
            $this->post($this->transactionRoute($kind, 'store', $season), $this->data($kind))->assertForbidden();
            $this->put($this->transactionRoute($kind, 'update', $season, $record), $this->data($kind))->assertForbidden();
            $this->delete($this->transactionRoute($kind, 'destroy', $season, $record), ['confirmed' => '1'])->assertForbidden();
            $this->assertModelExists($record);
        }
    }

    public function test_scoped_binding_rejects_wrong_land_season_and_transaction(): void
    {
        $season = PlantingSeason::factory()->create();
        $other = PlantingSeason::factory()->for($season->land)->create();
        $this->actingAs($season->land->user);
        $wrongLand = Land::factory()->for($season->land->user)->create();
        $this->get(route('farmer.finance.show', [$wrongLand, $season]))->assertNotFound();
        foreach (['expenses' => Expense::class, 'sales' => Sale::class] as $kind => $model) {
            $record = $model::factory()->for($other)->create();
            $this->get($this->transactionRoute($kind, 'edit', $season, $record))->assertNotFound();
            $this->put($this->transactionRoute($kind, 'update', $season, $record), $this->data($kind))->assertNotFound();
            $this->delete($this->transactionRoute($kind, 'destroy', $season, $record), ['confirmed' => '1'])->assertNotFound();
        }
    }

    public function test_guest_admin_and_inactive_accounts_cannot_use_finance(): void
    {
        $season = PlantingSeason::factory()->create();
        $urls = [route('farmer.finance.index'), route('farmer.finance.show', [$season->land, $season]), $this->transactionRoute('expenses', 'create', $season)];
        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('admin.dashboard'));
        }
        $this->actingAs(User::factory()->create(['is_active' => false]))->get(route('farmer.finance.index'))->assertRedirect(route('login'));
    }

    public function test_season_delete_confirms_and_cascades_finances_but_keeps_other_seasons(): void
    {
        $season = PlantingSeason::factory()->create();
        $expense = Expense::factory()->for($season)->create();
        $sale = Sale::factory()->for($season)->create();
        $otherExpense = Expense::factory()->create();
        $this->actingAs($season->land->user)->get(route('farmer.lands.planting-seasons.show', [$season->land, $season]))->assertOk()
            ->assertSee('1 pengeluaran')->assertSee('1 penjualan')->assertSee(route('farmer.finance.show', [$season->land, $season]), false);
        $url = route('farmer.lands.planting-seasons.destroy', [$season->land, $season]);
        $this->delete($url)->assertSessionHasErrors('confirmed');
        $this->assertModelExists($expense);
        $this->delete($url, ['confirmed' => '1'])->assertRedirect();
        $this->assertModelMissing($expense);
        $this->assertModelMissing($sale);
        $this->assertModelExists($otherExpense);
    }
}
