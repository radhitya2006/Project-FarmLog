<?php

namespace Tests\Feature\Farmer;

use App\Enums\ActivityStatus;
use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function activityRoute(string $action, PlantingSeason $season, ?Activity $activity = null, array $query = []): string
    {
        return route('farmer.lands.planting-seasons.activities.'.$action, [
            'land' => $season->land_id, 'planting_season' => $season,
            ...($activity ? ['activity' => $activity] : []), ...$query,
        ]);
    }

    private function validData(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Pemupukan pertama', 'scheduled_for' => '2026-04-12',
            'status' => 'direncanakan', 'notes' => 'Pupuk organik di pagi hari.',
        ], $overrides);
    }

    private function endpoints(PlantingSeason $season, Activity $activity): array
    {
        return [
            ['GET', $this->activityRoute('index', $season)],
            ['GET', $this->activityRoute('create', $season)],
            ['POST', $this->activityRoute('store', $season)],
            ['GET', $this->activityRoute('show', $season, $activity)],
            ['GET', $this->activityRoute('edit', $season, $activity)],
            ['PUT', $this->activityRoute('update', $season, $activity)],
            ['PATCH', $this->activityRoute('finish', $season, $activity)],
            ['DELETE', $this->activityRoute('destroy', $season, $activity)],
        ];
    }

    public function test_farmer_can_manage_activities_through_season_details(): void
    {
        $season = PlantingSeason::factory()->create();
        $this->actingAs($season->land->user)->get(route('farmer.lands.planting-seasons.show', [$season->land, $season]))
            ->assertOk()->assertSee($this->activityRoute('index', $season), false)->assertSee($this->activityRoute('create', $season), false);
        $this->get($this->activityRoute('index', $season))->assertOk()->assertSee('Belum ada kegiatan');
        $this->get($this->activityRoute('create', $season))->assertOk()->assertSee('Nama kegiatan');
        $response = $this->post($this->activityRoute('store', $season), $this->validData());
        $activity = $season->activities()->sole();
        $response->assertRedirect($this->activityRoute('show', $season, $activity))->assertSessionHas('success');
        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'planting_season_id' => $season->id, 'status' => 'direncanakan']);
        $this->get($this->activityRoute('show', $season, $activity))->assertOk()->assertSee('12/04/2026')
            ->assertSee('Pemupukan pertama')->assertSee('Tandai selesai')->assertSee('deleteActivityModal');
        $this->get($this->activityRoute('edit', $season, $activity))->assertOk()->assertSee('value="Pemupukan pertama"', false);
        $this->put($this->activityRoute('update', $season, $activity), $this->validData(['name' => 'Penyiraman', 'status' => 'selesai', 'notes' => '']))
            ->assertRedirect($this->activityRoute('show', $season, $activity))->assertSessionHas('success');
        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'name' => 'Penyiraman', 'status' => 'selesai', 'notes' => null]);
        $this->delete($this->activityRoute('destroy', $season, $activity), ['confirmed' => 1])
            ->assertRedirect($this->activityRoute('index', $season))->assertSessionHas('success');
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
        $this->assertDatabaseHas('planting_seasons', ['id' => $season->id]);
    }

    public function test_search_status_and_pagination_stay_scoped_to_season(): void
    {
        $season = PlantingSeason::factory()->create();
        $activities = Activity::factory()->count(13)->for($season)->create([
            'name' => 'Pemupukan', 'scheduled_for' => '2026-04-12',
        ]);
        Activity::factory()->finished()->for($season)->create(['name' => 'Pemupukan Selesai']);
        Activity::factory()->for($season)->create(['name' => 'Penyiraman']);
        $otherSeason = PlantingSeason::factory()->for($season->land)->create();
        Activity::factory()->for($otherSeason)->create(['name' => 'Pemupukan Musim Lain']);
        Activity::factory()->create(['name' => 'Pemupukan Petani Lain']);
        $filters = ['search' => 'Pemupukan', 'status' => 'direncanakan'];

        $this->actingAs($season->land->user)->get($this->activityRoute('index', $season, query: $filters))->assertOk()
            ->assertDontSee('Pemupukan Selesai')->assertDontSee('Pemupukan Musim Lain')->assertDontSee('Pemupukan Petani Lain')
            ->assertDontSee('Penyiraman')->assertSee('dari 13 kegiatan')
            ->assertViewHas('activities', fn ($page) => $page->count() === 12 && $page->total() === 13
                && $page->first()->is($activities->last()) && str_contains($page->nextPageUrl(), 'search=Pemupukan')
                && str_contains($page->nextPageUrl(), 'status=direncanakan'));
        $this->get($this->activityRoute('index', $season, query: [...$filters, 'page' => 2]))->assertOk()
            ->assertViewHas('activities', fn ($page) => $page->count() === 1 && $page->first()->is($activities->first()));
        $this->get($this->activityRoute('index', $season, query: ['status' => 'selesai']))->assertOk()
            ->assertViewHas('activities', fn ($page) => $page->total() === 1);
        $this->get($this->activityRoute('index', $season))->assertOk()->assertViewHas('activities', fn ($page) => $page->total() === 15);
    }

    public function test_list_orders_by_date_then_id_and_handles_empty_search(): void
    {
        $season = PlantingSeason::factory()->create();
        $latest = Activity::factory()->for($season)->create(['scheduled_for' => '2026-04-20']);
        Activity::factory()->for($season)->create(['scheduled_for' => '2026-04-01']);
        $this->actingAs($season->land->user)->get($this->activityRoute('index', $season))->assertOk()
            ->assertViewHas('activities', fn ($page) => $page->first()->is($latest));
        $this->get($this->activityRoute('index', $season, query: ['search' => 'Tidak Ada']))->assertOk()->assertSee('Kegiatan tidak ditemukan');
        $this->get($this->activityRoute('index', $season, query: ['search' => ['invalid']]))->assertSessionHasErrors('search');
        $this->get($this->activityRoute('index', $season, query: ['search' => str_repeat('a', 151)]))->assertSessionHasErrors('search');
        foreach (['invalid', ['selesai']] as $status) {
            $this->get($this->activityRoute('index', $season, query: ['status' => $status]))->assertSessionHasErrors('status');
        }
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_forms_cannot_create_or_change_activities(string $field, mixed $value): void
    {
        $season = PlantingSeason::factory()->create();
        $activity = Activity::factory()->for($season)->create();
        $data = $this->validData([$field => $value]);
        $this->actingAs($season->land->user)->post($this->activityRoute('store', $season), $data)->assertSessionHasErrors($field);
        $this->put($this->activityRoute('update', $season, $activity), $data)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('activities', 1);
        $this->assertDatabaseHas('activities', ['id' => $activity->id, 'name' => $activity->name, 'status' => $activity->status->value]);
    }

    public static function invalidFields(): array
    {
        return [
            'missing name' => ['name', ''],
            'long name' => ['name', str_repeat('a', 151)],
            'array name' => ['name', ['invalid']],
            'missing date' => ['scheduled_for', ''],
            'invalid date' => ['scheduled_for', '2026-02-30'],
            'wrong date format' => ['scheduled_for', '12/04/2026'],
            'missing status' => ['status', ''],
            'invalid status' => ['status', 'aktif'],
            'array status' => ['status', ['selesai']],
            'long notes' => ['notes', str_repeat('a', 5001)],
            'array notes' => ['notes', ['invalid']],
            'forged season' => ['planting_season_id', 999],
            'null season' => ['planting_season_id', null],
            'forged land' => ['land_id', 999],
            'null land' => ['land_id', null],
            'forged owner' => ['user_id', 999],
            'null owner' => ['user_id', null],
        ];
    }

    public function test_preparation_dates_and_maximum_lengths_and_optional_notes_are_allowed(): void
    {
        $season = PlantingSeason::factory()->create(['planted_at' => '2026-04-15']);
        $this->actingAs($season->land->user)->post($this->activityRoute('store', $season), $this->validData([
            'name' => str_repeat('a', 150), 'notes' => str_repeat('a', 5000), 'status' => 'selesai',
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $data = $this->validData();
        unset($data['notes']);
        $this->post($this->activityRoute('store', $season), $data)->assertSessionHasNoErrors();
        $this->assertNull($season->activities()->latest('id')->first()->notes);
        $this->assertDatabaseCount('activities', 2);
    }

    public function test_validation_keeps_input_and_notes_are_escaped(): void
    {
        $season = PlantingSeason::factory()->create();
        $this->actingAs($season->land->user)->from($this->activityRoute('create', $season))
            ->post($this->activityRoute('store', $season), $this->validData(['scheduled_for' => '']))
            ->assertRedirect($this->activityRoute('create', $season))->assertSessionHasInput('name', 'Pemupukan pertama');
        $this->withCookie(config('session.cookie'), session()->getId())->get($this->activityRoute('create', $season))
            ->assertOk()->assertSee('value="Pemupukan pertama"', false)->assertSee('id="scheduled_for-error"', false);
        $activity = Activity::factory()->for($season)->create(['notes' => '<script>alert(1)</script>']);
        $this->get($this->activityRoute('show', $season, $activity))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_finish_is_idempotent_and_edit_can_restore_planned_status(): void
    {
        $season = PlantingSeason::factory()->create();
        $activity = Activity::factory()->for($season)->create();
        $this->actingAs($season->land->user)->patch($this->activityRoute('finish', $season, $activity), [
            'status' => 'direncanakan', 'name' => 'Injected', 'planting_season_id' => 999,
        ])->assertRedirect($this->activityRoute('show', $season, $activity))->assertSessionHas('success');
        $this->assertSame(ActivityStatus::Finished, $activity->fresh()->status);
        $this->assertSame($season->id, $activity->fresh()->planting_season_id);
        $this->assertNotSame('Injected', $activity->fresh()->name);
        $updatedAt = $activity->fresh()->updated_at->toDateTimeString();
        $this->travel(1)->minutes();
        $this->patch($this->activityRoute('finish', $season, $activity))->assertSessionHas('success', 'Kegiatan sudah berstatus selesai.');
        $this->assertSame($updatedAt, $activity->fresh()->updated_at->toDateTimeString());
        $this->put($this->activityRoute('update', $season, $activity), $this->validData())->assertSessionHasNoErrors();
        $this->assertSame(ActivityStatus::Planned, $activity->fresh()->status);
    }

    public function test_delete_requires_confirmation_and_preserves_other_activities(): void
    {
        $season = PlantingSeason::factory()->create();
        $activity = Activity::factory()->for($season)->create();
        $other = Activity::factory()->for($season)->create();
        foreach ([[], ['confirmed' => 0]] as $data) {
            $this->actingAs($season->land->user)->delete($this->activityRoute('destroy', $season, $activity), $data)->assertSessionHasErrors('confirmed');
        }
        $this->assertDatabaseHas('activities', ['id' => $activity->id]);
        $this->delete($this->activityRoute('destroy', $season, $activity), ['confirmed' => 1])->assertRedirect();
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
        $this->assertDatabaseHas('activities', ['id' => $other->id]);
    }

    public function test_finished_season_is_read_only_and_reopening_restores_activity_operations(): void
    {
        $season = PlantingSeason::factory()->finished()->create();
        $activity = Activity::factory()->for($season)->create();
        $this->actingAs($season->land->user)->get($this->activityRoute('index', $season))->assertOk()
            ->assertSee('Kegiatan hanya dapat dilihat')->assertDontSee($this->activityRoute('create', $season), false);
        $this->get($this->activityRoute('show', $season, $activity))->assertOk()
            ->assertDontSee('deleteActivityModal')->assertDontSee('Tandai selesai')->assertDontSee('Edit kegiatan');
        foreach ($this->endpoints($season, $activity) as [$method, $url]) {
            if ($method !== 'GET' || $url === $this->activityRoute('create', $season) || $url === $this->activityRoute('edit', $season, $activity)) {
                $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertForbidden();
            }
        }
        $this->patch(route('farmer.lands.planting-seasons.reopen', [$season->land, $season]), ['confirmed' => 1])->assertRedirect();
        $this->assertSame(PlantingSeasonStatus::Active, $season->fresh()->status);
        $this->get($this->activityRoute('create', $season))->assertOk();
        $this->put($this->activityRoute('update', $season, $activity), $this->validData())->assertSessionHasNoErrors();
        $this->patch($this->activityRoute('finish', $season, $activity))->assertRedirect();
        $this->delete($this->activityRoute('destroy', $season, $activity), ['confirmed' => 1])->assertRedirect();
    }

    public function test_other_farmer_gets_403_for_all_activity_routes(): void
    {
        $season = PlantingSeason::factory()->create();
        $activity = Activity::factory()->for($season)->create();
        $this->actingAs(User::factory()->create());
        foreach ($this->endpoints($season, $activity) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertForbidden();
        }
        $this->assertDatabaseCount('activities', 1);
    }

    public function test_mismatched_parents_and_missing_resources_return_404(): void
    {
        $season = PlantingSeason::factory()->create();
        $other = PlantingSeason::factory()->for($season->land)->create();
        $activity = Activity::factory()->for($other)->create();
        $this->actingAs($season->land->user);
        foreach (array_slice($this->endpoints($season, $activity), 3) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertNotFound();
        }
        $wrongLand = Land::factory()->for($season->land->user)->create();
        $this->get(route('farmer.lands.planting-seasons.activities.index', [$wrongLand, $season]))->assertNotFound();
        $missing = new Activity;
        $missing->id = 999999;
        foreach (array_slice($this->endpoints($season, $missing), 3) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertNotFound();
        }
        $this->get(route('farmer.lands.planting-seasons.activities.index', [$season->land_id, 999999]))->assertNotFound();
    }

    public function test_guests_admin_and_inactive_users_cannot_use_activity_routes(): void
    {
        $season = PlantingSeason::factory()->create();
        $activity = Activity::factory()->for($season)->create();
        foreach ($this->endpoints($season, $activity) as [$method, $url]) {
            $this->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('login'));
        }
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        foreach ($this->endpoints($season, $activity) as [$method, $url]) {
            $this->actingAs($admin)->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('admin.dashboard'));
        }
        $inactive = User::factory()->inactive()->create();
        foreach ($this->endpoints($season, $activity) as [$method, $url]) {
            $this->actingAs($inactive)->call($method, $url, [...$this->validData(), 'confirmed' => 1])->assertRedirect(route('login'));
            $this->assertGuest();
        }
        $this->assertDatabaseCount('activities', 1);
    }
}
