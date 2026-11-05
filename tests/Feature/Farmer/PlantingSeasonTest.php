<?php

namespace Tests\Feature\Farmer;

use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PlantingSeasonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function validData(array $overrides = []): array
    {
        return array_replace([
            'crop_name' => 'Padi', 'planted_at' => '2026-01-10',
            'expected_harvest_at' => '2026-05-10', 'notes' => 'Irigasi terjadwal.',
        ], $overrides);
    }

    private function seasonRoute(string $action, Land $land, ?PlantingSeason $season = null, array $query = []): string
    {
        return route('farmer.lands.planting-seasons.'.$action, [
            'land' => $land, ...($season ? ['planting_season' => $season] : []), ...$query,
        ]);
    }

    private function endpoints(Land $land, PlantingSeason $season): array
    {
        return [
            ['GET', $this->seasonRoute('index', $land)],
            ['GET', $this->seasonRoute('create', $land)],
            ['POST', $this->seasonRoute('store', $land)],
            ['GET', $this->seasonRoute('show', $land, $season)],
            ['GET', $this->seasonRoute('edit', $land, $season)],
            ['PUT', $this->seasonRoute('update', $land, $season)],
            ['PATCH', $this->seasonRoute('finish', $land, $season)],
            ['PATCH', $this->seasonRoute('reopen', $land, $season)],
            ['DELETE', $this->seasonRoute('destroy', $land, $season)],
        ];
    }

    public function test_farmer_can_create_view_and_edit_season_from_land(): void
    {
        $land = Land::factory()->create();
        $this->actingAs($land->user)->get(route('farmer.lands.show', $land))->assertOk()
            ->assertSee($this->seasonRoute('index', $land), false)->assertSee($this->seasonRoute('create', $land), false);
        $this->get($this->seasonRoute('create', $land))->assertOk()->assertSee('Komoditas');
        $response = $this->post($this->seasonRoute('store', $land), $this->validData());
        $season = $land->plantingSeasons()->sole();
        $response->assertRedirect($this->seasonRoute('show', $land, $season))->assertSessionHas('success');
        $this->assertSame(PlantingSeasonStatus::Active, $season->status);
        $this->get($this->seasonRoute('show', $land, $season))->assertOk()->assertSee('Padi')->assertSee('10/01/2026')
            ->assertSee('id="finishSeasonModal"', false)->assertSee('id="deleteSeasonModal"', false)
            ->assertSee('name="confirmed" value="1"', false);
        $this->get($this->seasonRoute('edit', $land, $season))->assertOk()->assertSee('value="2026-01-10"', false);
        $this->put($this->seasonRoute('update', $land, $season), $this->validData(['crop_name' => 'Jagung', 'notes' => '']))
            ->assertRedirect($this->seasonRoute('show', $land, $season))->assertSessionHas('success');
        $this->assertDatabaseHas('planting_seasons', ['id' => $season->id, 'crop_name' => 'Jagung', 'notes' => null, 'land_id' => $land->id]);
    }

    public function test_list_is_scoped_to_land_filters_status_and_paginates_latest_first(): void
    {
        $land = Land::factory()->create();
        $seasons = PlantingSeason::factory()->count(13)->for($land)->create(['crop_name' => 'Padi']);
        PlantingSeason::factory()->finished()->for($land)->create(['crop_name' => 'Jagung Selesai']);
        $otherLand = Land::factory()->for($land->user)->create();
        PlantingSeason::factory()->for($otherLand)->create(['crop_name' => 'Lahan Lain']);
        PlantingSeason::factory()->create(['crop_name' => 'Petani Lain']);
        $this->actingAs($land->user)->get($this->seasonRoute('index', $land, query: ['status' => 'aktif']))->assertOk()
            ->assertDontSee('Lahan Lain')->assertDontSee('Petani Lain')->assertDontSee('Jagung Selesai')
            ->assertSee('dari 13 musim tanam')->assertSee('Berikutnya')
            ->assertViewHas('seasons', fn ($page) => $page->count() === 12 && $page->total() === 13
                && $page->first()->is($seasons->last()) && str_contains($page->nextPageUrl(), 'status=aktif'));
        $this->get($this->seasonRoute('index', $land, query: ['status' => 'aktif', 'page' => 2]))->assertOk()
            ->assertViewHas('seasons', fn ($page) => $page->count() === 1 && $page->first()->is($seasons->first()));
        $this->get($this->seasonRoute('index', $land, query: ['status' => 'selesai']))->assertOk()->assertSee('Jagung Selesai')
            ->assertViewHas('seasons', fn ($page) => $page->total() === 1);
        $this->get($this->seasonRoute('index', $land))->assertOk()->assertViewHas('seasons', fn ($page) => $page->total() === 14);
    }

    public function test_empty_states_and_invalid_status_filters(): void
    {
        $land = Land::factory()->create();
        $this->actingAs($land->user)->get($this->seasonRoute('index', $land))->assertOk()->assertSee('Mulai musim tanam pertama');
        $this->get($this->seasonRoute('index', $land, query: ['status' => 'selesai']))->assertOk()->assertSee('Musim tanam tidak ditemukan');
        foreach (['invalid', ['aktif']] as $status) {
            $this->get($this->seasonRoute('index', $land, query: ['status' => $status]))->assertSessionHasErrors('status');
        }
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_data_is_rejected_for_create_and_update(string $field, mixed $value): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $data = $this->validData([$field => $value]);
        $this->actingAs($land->user)->post($this->seasonRoute('store', $land), $data)->assertSessionHasErrors($field);
        $this->put($this->seasonRoute('update', $land, $season), $data)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('planting_seasons', 1);
        $this->assertDatabaseHas('planting_seasons', ['id' => $season->id, 'crop_name' => $season->crop_name]);
    }

    public static function invalidFields(): array
    {
        return [
            'required crop' => ['crop_name', ''],
            'long crop' => ['crop_name', str_repeat('a', 151)],
            'array crop' => ['crop_name', ['Padi']],
            'required planting date' => ['planted_at', ''],
            'invalid planting date' => ['planted_at', '2026-02-30'],
            'wrong date format' => ['planted_at', '10/01/2026'],
            'required harvest estimate' => ['expected_harvest_at', ''],
            'invalid harvest estimate' => ['expected_harvest_at', '2026-13-01'],
            'estimate before planting' => ['expected_harvest_at', '2026-01-09'],
            'long notes' => ['notes', str_repeat('a', 5001)],
            'array notes' => ['notes', ['invalid']],
            'forged land' => ['land_id', 999],
            'null land' => ['land_id', null],
            'forged owner' => ['user_id', 999],
            'null owner' => ['user_id', null],
            'forged status' => ['status', 'selesai'],
            'null status' => ['status', null],
        ];
    }

    public function test_boundary_values_same_day_estimate_optional_notes_and_multiple_active_seasons(): void
    {
        $land = Land::factory()->create();
        $data = $this->validData(['crop_name' => str_repeat('a', 150), 'notes' => str_repeat('a', 5000), 'expected_harvest_at' => '2026-01-10']);
        $this->actingAs($land->user)->post($this->seasonRoute('store', $land), $data)->assertSessionHasNoErrors()->assertRedirect();
        unset($data['notes']);
        $this->post($this->seasonRoute('store', $land), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(2, $land->plantingSeasons()->where('status', 'aktif')->count());
        $this->assertNull($land->plantingSeasons()->latest('id')->first()->notes);
    }

    public function test_validation_keeps_values_and_escapes_notes(): void
    {
        $land = Land::factory()->create();
        $this->actingAs($land->user)->from($this->seasonRoute('create', $land))
            ->post($this->seasonRoute('store', $land), $this->validData(['expected_harvest_at' => '2026-01-01']))
            ->assertSessionHasInput('crop_name', 'Padi');
        $this->withCookie(config('session.cookie'), session()->getId())->get($this->seasonRoute('create', $land))
            ->assertOk()->assertSee('value="Padi"', false)->assertSee('id="expected_harvest_at-error"', false);
        $season = PlantingSeason::factory()->for($land)->create(['notes' => '<script>alert(1)</script>']);
        $this->get($this->seasonRoute('show', $land, $season))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_planting_date_cannot_invalidate_existing_harvests(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create(['planted_at' => '2026-01-10']);
        Harvest::factory()->for($season)->create(['harvested_at' => '2026-03-01']);
        $this->actingAs($land->user)->put($this->seasonRoute('update', $land, $season), $this->validData(['planted_at' => '2026-03-02']))
            ->assertSessionHasErrors('planted_at');
        $this->assertSame('2026-01-10', $season->fresh()->planted_at->toDateString());
        $this->put($this->seasonRoute('update', $land, $season), $this->validData(['planted_at' => '2026-03-01']))
            ->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_finishing_locks_season_and_children_and_reopening_unlocks_them(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $activity = Activity::factory()->for($season)->create();
        $harvest = Harvest::factory()->for($season)->create();
        $this->actingAs($land->user)->patch($this->seasonRoute('finish', $land, $season), ['confirmed' => 1])
            ->assertRedirect($this->seasonRoute('show', $land, $season))->assertSessionHas('success');
        $this->assertSame(PlantingSeasonStatus::Finished, $season->fresh()->status);
        $this->get($this->seasonRoute('show', $land, $season))->assertOk()->assertSee('Musim ini sudah selesai')
            ->assertSee('id="reopenSeasonModal"', false)->assertDontSee($this->seasonRoute('edit', $land, $season), false);
        $this->get($this->seasonRoute('edit', $land, $season))->assertForbidden();
        $this->put($this->seasonRoute('update', $land, $season), $this->validData())->assertForbidden();
        foreach ([$activity, $harvest] as $resource) {
            $this->assertTrue($land->user->can('view', $resource));
            $this->assertFalse($land->user->can('create', [$resource::class, $season->fresh()]));
            $this->assertFalse($land->user->can('update', $resource));
            $this->assertFalse($land->user->can('delete', $resource));
        }
        $this->patch($this->seasonRoute('reopen', $land, $season), ['confirmed' => 1])->assertRedirect();
        $this->assertSame(PlantingSeasonStatus::Active, $season->fresh()->status);
        $this->get($this->seasonRoute('edit', $land, $season))->assertOk();
        $this->put($this->seasonRoute('update', $land, $season), $this->validData())->assertSessionHasNoErrors();
        foreach ([$activity, $harvest] as $resource) {
            $this->assertTrue($land->user->can('update', $resource));
            $this->assertTrue($land->user->can('create', [$resource::class, $season->fresh()]));
        }
        $this->assertDatabaseHas('activities', ['id' => $activity->id]);
        $this->assertDatabaseHas('harvests', ['id' => $harvest->id]);
    }

    public function test_repeated_status_action_is_a_no_op_and_payload_cannot_edit_identity(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $timestamp = $season->updated_at->toDateTimeString();
        $this->travel(1)->minutes();
        $this->actingAs($land->user)->patch($this->seasonRoute('reopen', $land, $season), [
            'confirmed' => 1, 'crop_name' => 'Injected', 'status' => 'selesai', 'land_id' => 999,
        ])->assertSessionHas('success', 'Musim tanam sudah berstatus aktif.');
        $this->assertSame($timestamp, $season->fresh()->updated_at->toDateTimeString());
        $this->assertSame($land->id, $season->fresh()->land_id);
        $this->assertNotSame('Injected', $season->fresh()->crop_name);
        $season->update(['status' => PlantingSeasonStatus::Finished]);
        $this->patch($this->seasonRoute('finish', $land, $season), ['confirmed' => 1])
            ->assertSessionHas('success', 'Musim tanam sudah berstatus selesai.');
    }

    public function test_status_actions_and_delete_require_confirmation(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $this->actingAs($land->user);
        foreach (['finish', 'reopen', 'destroy'] as $action) {
            foreach ([[], ['confirmed' => 0]] as $data) {
                $this->call($action === 'destroy' ? 'DELETE' : 'PATCH', $this->seasonRoute($action, $land, $season), $data)
                    ->assertSessionHasErrors('confirmed');
            }
        }
        $this->assertSame(PlantingSeasonStatus::Active, $season->fresh()->status);
    }

    public function test_delete_active_or_finished_season_cascades_only_its_children(): void
    {
        $land = Land::factory()->create();
        $other = PlantingSeason::factory()->for($land)->create();
        $otherActivity = Activity::factory()->for($other)->create();
        $otherHarvest = Harvest::factory()->for($other)->create();
        foreach ([PlantingSeasonStatus::Active, PlantingSeasonStatus::Finished] as $status) {
            $season = PlantingSeason::factory()->for($land)->create(['status' => $status]);
            $activity = Activity::factory()->for($season)->create();
            $harvest = Harvest::factory()->for($season)->create();
            $this->actingAs($land->user)->get($this->seasonRoute('show', $land, $season))->assertOk()
                ->assertViewHas('season', fn ($value) => $value->activities_count === 1 && $value->harvests_count === 1)
                ->assertSee('1 kegiatan')->assertSee('1 catatan panen');
            $this->delete($this->seasonRoute('destroy', $land, $season), ['confirmed' => 1])
                ->assertRedirect($this->seasonRoute('index', $land))->assertSessionHas('success');
            $this->assertDatabaseMissing('planting_seasons', ['id' => $season->id]);
            $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
            $this->assertDatabaseMissing('harvests', ['id' => $harvest->id]);
        }
        $this->assertDatabaseHas('planting_seasons', ['id' => $other->id]);
        $this->assertDatabaseHas('activities', ['id' => $otherActivity->id]);
        $this->assertDatabaseHas('harvests', ['id' => $otherHarvest->id]);
        $this->delete($this->seasonRoute('destroy', $land, $other), ['confirmed' => 1])->assertRedirect();
        $this->delete(route('farmer.lands.destroy', $land))->assertRedirect(route('farmer.lands.index'));
        $this->assertDatabaseMissing('lands', ['id' => $land->id]);
    }

    public function test_other_farmers_cannot_access_any_season_endpoint(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $this->actingAs(User::factory()->create());
        foreach ($this->endpoints($land, $season) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertForbidden();
        }
        $this->assertDatabaseCount('planting_seasons', 1);
    }

    public function test_mismatched_land_season_and_nonexistent_resources_return_404(): void
    {
        $land = Land::factory()->create();
        $otherLand = Land::factory()->for($land->user)->create();
        $season = PlantingSeason::factory()->for($otherLand)->create();
        $this->actingAs($land->user);
        foreach (array_slice($this->endpoints($land, $season), 3) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertNotFound();
        }
        $missing = new PlantingSeason;
        $missing->id = 999999;
        foreach (array_slice($this->endpoints($land, $missing), 3) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertNotFound();
        }
        $this->get(route('farmer.lands.planting-seasons.index', ['land' => 999999]))->assertNotFound();
    }

    public function test_guests_admins_and_inactive_users_cannot_use_season_routes(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $endpoints = $this->endpoints($land, $season);
        foreach ($endpoints as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('login'));
        }
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        foreach ($endpoints as [$method, $url]) {
            $this->actingAs($admin)->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('admin.dashboard'));
        }
        $inactive = User::factory()->inactive()->create();
        foreach ($endpoints as [$method, $url]) {
            $this->actingAs($inactive)->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('login'));
            $this->assertGuest();
        }
        $this->assertDatabaseCount('planting_seasons', 1);
    }
}
