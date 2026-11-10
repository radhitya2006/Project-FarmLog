<?php

namespace Tests\Feature\Farmer;

use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HarvestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function harvestRoute(string $action, PlantingSeason $season, ?Harvest $harvest = null, array $query = []): string
    {
        return route('farmer.lands.planting-seasons.harvests.'.$action, [
            'land' => $season->land_id, 'planting_season' => $season,
            ...($harvest ? ['harvest' => $harvest] : []), ...$query,
        ]);
    }

    private function validData(array $overrides = []): array
    {
        return array_replace(['harvested_at' => '2026-04-10', 'quantity' => '150.25', 'unit' => 'kg', 'notes' => 'Panen pertama.'], $overrides);
    }

    private function endpoints(PlantingSeason $season, Harvest $harvest): array
    {
        return [
            ['GET', $this->harvestRoute('index', $season)], ['GET', $this->harvestRoute('create', $season)],
            ['POST', $this->harvestRoute('store', $season)], ['GET', $this->harvestRoute('show', $season, $harvest)],
            ['GET', $this->harvestRoute('edit', $season, $harvest)], ['PUT', $this->harvestRoute('update', $season, $harvest)],
            ['DELETE', $this->harvestRoute('destroy', $season, $harvest)],
        ];
    }

    public function test_farmer_can_create_view_edit_and_delete_multiple_harvests(): void
    {
        $season = PlantingSeason::factory()->create(['planted_at' => '2026-01-10']);
        $this->actingAs($season->land->user)->get(route('farmer.lands.planting-seasons.show', [$season->land, $season]))->assertOk()
            ->assertSee($this->harvestRoute('index', $season), false)->assertSee($this->harvestRoute('create', $season), false);
        $this->get($this->harvestRoute('index', $season))->assertOk()->assertSee('Belum ada catatan panen');
        $this->get($this->harvestRoute('create', $season))->assertOk()->assertSee('Jumlah hasil panen');
        $response = $this->post($this->harvestRoute('store', $season), $this->validData());
        $harvest = $season->harvests()->sole();
        $response->assertRedirect($this->harvestRoute('show', $season, $harvest))->assertSessionHas('success');
        $this->get($this->harvestRoute('show', $season, $harvest))->assertOk()->assertSee('150,25 kg')->assertSee('deleteHarvestModal');
        $this->get($this->harvestRoute('edit', $season, $harvest))->assertOk()->assertSee('value="150.25"', false);
        $this->put($this->harvestRoute('update', $season, $harvest), $this->validData(['quantity' => '2.50', 'unit' => 'ton', 'notes' => '']))
            ->assertRedirect($this->harvestRoute('show', $season, $harvest))->assertSessionHas('success');
        $this->assertDatabaseHas('harvests', ['id' => $harvest->id, 'quantity' => '2.50', 'unit' => 'ton', 'notes' => null]);
        $this->post($this->harvestRoute('store', $season), $this->validData(['quantity' => '50']))->assertSessionHasNoErrors();
        $this->assertSame(2, $season->harvests()->count());
        $this->delete($this->harvestRoute('destroy', $season, $harvest), ['confirmed' => 1])
            ->assertRedirect($this->harvestRoute('index', $season))->assertSessionHas('success');
        $this->assertDatabaseMissing('harvests', ['id' => $harvest->id]);
        $this->get($this->harvestRoute('index', $season))->assertOk()
            ->assertViewHas('season', fn ($model) => $model->total_kg === '50.00' && $model->total_ton === null);
    }

    public function test_list_shows_all_units_and_paginates_without_changing_whole_season_totals(): void
    {
        $season = PlantingSeason::factory()->create(['planted_at' => '2026-01-01']);
        $harvests = Harvest::factory()->count(13)->for($season)->create(['quantity' => '0.10', 'unit' => 'kg', 'harvested_at' => '2026-04-10']);
        $tonHarvest = Harvest::factory()->for($season)->create(['quantity' => '2.25', 'unit' => 'ton', 'harvested_at' => '2026-05-10']);
        $otherSeason = PlantingSeason::factory()->for($season->land)->create();
        Harvest::factory()->for($otherSeason)->create(['quantity' => '999']);
        $this->actingAs($season->land->user)->get($this->harvestRoute('index', $season))->assertOk()
            ->assertViewHas('harvests', fn ($page) => $page->total() === 14 && $page->count() === 12
                && $page->first()->is($tonHarvest) && $page->get(1)->is($harvests->last()))
            ->assertViewHas('season', fn ($model) => $model->total_kg === '1.30' && $model->total_ton === '2.25' && $model->harvests_count === 14)
            ->assertSee('1,30 kg')->assertSee('2,25 ton')->assertDontSee('name="unit"', false);
        $this->get($this->harvestRoute('index', $season, query: ['page' => 2]))->assertOk()
            ->assertViewHas('harvests', fn ($page) => $page->count() === 2 && $page->last()->is($harvests->first()));
        foreach (['kg', 'ton', 'liter', ['kg']] as $unit) {
            $this->get($this->harvestRoute('index', $season, query: ['unit' => $unit]))->assertOk()->assertSessionHasNoErrors()
                ->assertViewHas('harvests', fn ($page) => $page->total() === 14 && ! str_contains($page->nextPageUrl(), 'unit='));
        }
    }

    public function test_totals_keep_decimal_precision_and_separate_units_on_season_details(): void
    {
        $season = PlantingSeason::factory()->create();
        Harvest::factory()->count(2)->for($season)->create(['quantity' => '9999999999.99', 'unit' => 'kg']);
        Harvest::factory()->for($season)->create(['quantity' => '0.10', 'unit' => 'ton']);
        Harvest::factory()->for($season)->create(['quantity' => '0.20', 'unit' => 'ton']);
        $this->actingAs($season->land->user)->get(route('farmer.lands.planting-seasons.show', [$season->land, $season]))->assertOk()
            ->assertSee('19.999.999.999,98 kg')->assertSee('0,30 ton')
            ->assertViewHas('season', fn ($model) => $model->harvests_count === 4 && $model->total_kg === '19999999999.98' && $model->total_ton === '0.30');
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_data_is_rejected_on_create_and_update(string $field, mixed $value): void
    {
        $season = PlantingSeason::factory()->create(['planted_at' => '2026-01-10']);
        $harvest = Harvest::factory()->for($season)->create();
        $this->actingAs($season->land->user)->post($this->harvestRoute('store', $season), $this->validData([$field => $value]))
            ->assertSessionHasErrors($field);
        $this->put($this->harvestRoute('update', $season, $harvest), $this->validData([$field => $value]))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('harvests', 1);
        $this->assertDatabaseHas('harvests', ['id' => $harvest->id, 'quantity' => $harvest->quantity, 'unit' => $harvest->unit->value]);
    }

    public static function invalidFields(): array
    {
        return [
            'missing date' => ['harvested_at', ''], 'invalid date' => ['harvested_at', '2026-02-30'],
            'before planting' => ['harvested_at', '2026-01-09'], 'wrong format' => ['harvested_at', '10/04/2026'],
            'missing quantity' => ['quantity', ''], 'zero' => ['quantity', '0'], 'negative' => ['quantity', '-1'],
            'too precise' => ['quantity', '1.001'], 'scientific' => ['quantity', '1e2'],
            'too large' => ['quantity', '10000000000'], 'not numeric' => ['quantity', 'hasil'],
            'missing unit' => ['unit', ''], 'invalid unit' => ['unit', 'liter'], 'array unit' => ['unit', ['kg']],
            'long notes' => ['notes', str_repeat('a', 5001)], 'array notes' => ['notes', ['invalid']],
            'forged season' => ['planting_season_id', 999], 'null season' => ['planting_season_id', null],
            'forged owner' => ['user_id', 999], 'null owner' => ['user_id', null],
            'forged land' => ['land_id', 999], 'null land' => ['land_id', null],
        ];
    }

    public function test_boundaries_same_day_harvest_and_optional_notes_are_allowed(): void
    {
        $season = PlantingSeason::factory()->create(['planted_at' => '2026-04-10']);
        foreach (['0.01', '9999999999.99', '1'] as $quantity) {
            $data = $this->validData(['quantity' => $quantity]);
            unset($data['notes']);
            $this->actingAs($season->land->user)->post($this->harvestRoute('store', $season), $data)->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertDatabaseCount('harvests', 3);
        $this->assertNull($season->harvests()->first()->notes);
        $this->post($this->harvestRoute('store', $season), $this->validData(['notes' => str_repeat('a', 5000)]))->assertSessionHasNoErrors();
    }

    public function test_validation_preserves_values_and_notes_are_escaped(): void
    {
        $season = PlantingSeason::factory()->create(['planted_at' => '2026-01-10']);
        $this->actingAs($season->land->user)->from($this->harvestRoute('create', $season))
            ->post($this->harvestRoute('store', $season), $this->validData(['quantity' => '0']))
            ->assertSessionHasInput('harvested_at', '2026-04-10');
        $this->withCookie(config('session.cookie'), session()->getId())->get($this->harvestRoute('create', $season))->assertOk()
            ->assertSee('value="2026-04-10"', false)->assertSee('id="quantity-error"', false);
        $harvest = Harvest::factory()->for($season)->create(['notes' => '<script>alert(1)</script>']);
        $this->get($this->harvestRoute('show', $season, $harvest))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_delete_needs_confirmation_and_does_not_remove_other_records(): void
    {
        $season = PlantingSeason::factory()->create();
        $harvest = Harvest::factory()->for($season)->create();
        $other = Harvest::factory()->for($season)->create();
        foreach ([[], ['confirmed' => 0]] as $data) {
            $this->actingAs($season->land->user)->delete($this->harvestRoute('destroy', $season, $harvest), $data)->assertSessionHasErrors('confirmed');
        }
        $this->assertDatabaseHas('harvests', ['id' => $harvest->id]);
        $this->delete($this->harvestRoute('destroy', $season, $harvest), ['confirmed' => 1])->assertRedirect();
        $this->assertDatabaseHas('harvests', ['id' => $other->id]);
        $this->assertDatabaseHas('planting_seasons', ['id' => $season->id]);
    }

    public function test_finished_seasons_allow_reading_but_require_reopening_for_changes(): void
    {
        $season = PlantingSeason::factory()->finished()->create(['planted_at' => '2026-01-10']);
        $harvest = Harvest::factory()->for($season)->create();
        $this->actingAs($season->land->user)->get($this->harvestRoute('index', $season))->assertOk()->assertSee('Panen hanya dapat dilihat')
            ->assertDontSee($this->harvestRoute('create', $season), false);
        $this->get($this->harvestRoute('show', $season, $harvest))->assertOk()->assertDontSee('deleteHarvestModal')
            ->assertDontSee('Edit catatan panen');
        foreach ($this->endpoints($season, $harvest) as [$method, $url]) {
            if ($method !== 'GET' || $url === $this->harvestRoute('create', $season) || $url === $this->harvestRoute('edit', $season, $harvest)) {
                $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertForbidden();
            }
        }
        $this->patch(route('farmer.lands.planting-seasons.reopen', [$season->land, $season]), ['confirmed' => 1])->assertRedirect();
        $this->assertSame(PlantingSeasonStatus::Active, $season->fresh()->status);
        $this->get($this->harvestRoute('create', $season))->assertOk();
        $this->put($this->harvestRoute('update', $season, $harvest), $this->validData())->assertSessionHasNoErrors();
        $this->delete($this->harvestRoute('destroy', $season, $harvest), ['confirmed' => 1])->assertRedirect();
    }

    public function test_other_farmer_cannot_access_any_harvest_route(): void
    {
        $season = PlantingSeason::factory()->create();
        $harvest = Harvest::factory()->for($season)->create();
        $this->actingAs(User::factory()->create());
        foreach ($this->endpoints($season, $harvest) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertForbidden();
        }
        $this->assertDatabaseCount('harvests', 1);
    }

    public function test_mismatched_parents_and_missing_harvests_return_404(): void
    {
        $season = PlantingSeason::factory()->create();
        $otherSeason = PlantingSeason::factory()->for($season->land)->create();
        $harvest = Harvest::factory()->for($otherSeason)->create();
        $this->actingAs($season->land->user);
        foreach (array_slice($this->endpoints($season, $harvest), 3) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertNotFound();
        }
        $otherLand = Land::factory()->for($season->land->user)->create();
        $this->get(route('farmer.lands.planting-seasons.harvests.index', [$otherLand, $season]))->assertNotFound();
        $missing = new Harvest;
        $missing->id = 999999;
        foreach (array_slice($this->endpoints($season, $missing), 3) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertNotFound();
        }
    }

    public function test_guests_admins_and_inactive_users_cannot_use_harvest_routes(): void
    {
        $season = PlantingSeason::factory()->create();
        $harvest = Harvest::factory()->for($season)->create();
        foreach ($this->endpoints($season, $harvest) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('login'));
        }
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        foreach ($this->endpoints($season, $harvest) as [$method, $url]) {
            $this->actingAs($admin)->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('admin.dashboard'));
        }
        $inactive = User::factory()->inactive()->create();
        foreach ($this->endpoints($season, $harvest) as [$method, $url]) {
            $this->actingAs($inactive)->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('login'));
            $this->assertGuest();
        }
        $this->assertDatabaseCount('harvests', 1);
    }
}
