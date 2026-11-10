<?php

namespace Tests\Feature\Farmer;

use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_counts_and_cards_only_include_own_lands_and_active_seasons(): void
    {
        $farmer = User::factory()->create();
        $land = Land::factory()->for($farmer)->create(['name' => 'Sawah Utara']);
        $secondLand = Land::factory()->for($farmer)->create(['name' => 'Kebun Selatan']);
        Land::factory()->for($farmer)->create();
        PlantingSeason::factory()->for($land)->create(['crop_name' => 'Padi Aktif']);
        PlantingSeason::factory()->for($secondLand)->create(['crop_name' => 'Cabai Aktif']);
        PlantingSeason::factory()->finished()->for($land)->create(['crop_name' => 'Riwayat Selesai']);
        PlantingSeason::factory()->create(['crop_name' => 'Rahasia Petani Lain']);

        $this->actingAs($farmer)->get(route('farmer.dashboard'))->assertOk()
            ->assertViewHas('landCount', 3)->assertViewHas('activeSeasonCount', 2)
            ->assertViewHas('activeSeasons', fn ($page) => $page->total() === 2
                && $page->every(fn ($season) => $season->relationLoaded('land') && in_array($season->land_id, [$land->id, $secondLand->id])))
            ->assertSee('Padi Aktif')->assertSee('Cabai Aktif')->assertSee('Sawah Utara')->assertSee('Kebun Selatan')
            ->assertDontSee('Riwayat Selesai')->assertDontSee('Rahasia Petani Lain');
    }

    public function test_active_seasons_are_paginated_by_planting_date_then_id_and_keep_anchor(): void
    {
        $farmer = User::factory()->create();
        $land = Land::factory()->for($farmer)->create();
        $oldest = PlantingSeason::factory()->for($land)->create(['planted_at' => '2026-01-01']);
        PlantingSeason::factory()->count(5)->for($land)->create(['planted_at' => '2026-04-01']);
        $newest = PlantingSeason::factory()->for($land)->create(['planted_at' => '2026-04-01']);

        $this->actingAs($farmer)->get(route('farmer.dashboard'))->assertOk()
            ->assertViewHas('activeSeasonCount', 7)
            ->assertViewHas('activeSeasons', fn ($page) => $page->count() === 6 && $page->first()->is($newest)
                && str_ends_with($page->nextPageUrl(), 'page=2#active-seasons'))
            ->assertSee('dari 7 musim aktif');
        $this->get(route('farmer.dashboard', ['page' => 2]))->assertOk()
            ->assertViewHas('activeSeasons', fn ($page) => $page->count() === 1 && $page->first()->is($oldest))
            ->assertViewHas('activeSeasonCount', 7);
    }

    public function test_quick_actions_and_card_links_point_to_the_right_season(): void
    {
        $season = PlantingSeason::factory()->create([
            'planted_at' => '2026-04-01', 'expected_harvest_at' => '2026-07-10',
        ]);
        $this->actingAs($season->land->user)->get(route('farmer.dashboard'))->assertOk()
            ->assertSee('href="#active-seasons"', false)->assertSee('id="active-seasons"', false)
            ->assertSee('01/04/2026')->assertSee('10/07/2026')
            ->assertSee(route('farmer.lands.index'), false)
            ->assertSee(route('farmer.lands.planting-seasons.show', [$season->land, $season]), false)
            ->assertSee(route('farmer.lands.planting-seasons.activities.index', [$season->land, $season]), false)
            ->assertSee(route('farmer.lands.planting-seasons.activities.create', [$season->land, $season]), false);
        $this->get(route('farmer.lands.planting-seasons.activities.create', [$season->land, $season]))
            ->assertOk()->assertViewHas('season', fn ($model) => $model->is($season));
    }

    public function test_farmer_without_lands_gets_setup_guidance_and_disabled_activity_action(): void
    {
        $this->actingAs(User::factory()->create())->get(route('farmer.dashboard'))->assertOk()
            ->assertViewHas('landCount', 0)->assertViewHas('activeSeasonCount', 0)
            ->assertSee('Mulai dari lahan pertama Anda')->assertSee(route('farmer.lands.create'), false)
            ->assertSee('type="button" disabled aria-describedby="quick-activity-help"', false);
    }

    public function test_farmer_with_land_but_no_active_season_gets_land_guidance(): void
    {
        $land = Land::factory()->create();
        $this->actingAs($land->user)->get(route('farmer.dashboard'))->assertOk()
            ->assertViewHas('landCount', 1)->assertViewHas('activeSeasonCount', 0)
            ->assertSee('Belum ada musim tanam aktif')->assertSee('Buka lahan saya')
            ->assertSee('type="button" disabled aria-describedby="quick-activity-help"', false);
        PlantingSeason::factory()->finished()->for($land)->create();
        $this->get(route('farmer.dashboard'))->assertOk()->assertSee('Belum ada musim tanam aktif')
            ->assertViewHas('activeSeasonCount', 0);
    }

    public function test_out_of_range_page_links_back_to_active_seasons_without_showing_false_empty_state(): void
    {
        $season = PlantingSeason::factory()->create();
        $this->actingAs($season->land->user)->get(route('farmer.dashboard', ['page' => 10]))->assertOk()
            ->assertViewHas('activeSeasonCount', 1)->assertSee('Tidak ada musim di halaman ini')
            ->assertSee(route('farmer.dashboard').'#active-seasons', false)->assertDontSee('Belum ada musim tanam aktif');
    }

    public function test_activity_link_is_rechecked_when_a_displayed_season_finishes(): void
    {
        $season = PlantingSeason::factory()->create();
        $url = route('farmer.lands.planting-seasons.activities.create', [$season->land, $season]);
        $this->actingAs($season->land->user)->get(route('farmer.dashboard'))->assertOk()->assertSee($url, false);
        $season->update(['status' => PlantingSeasonStatus::Finished]);
        $this->get($url)->assertForbidden();
        $this->post(route('farmer.lands.planting-seasons.activities.store', [$season->land, $season]), [
            'name' => 'Kegiatan baru', 'scheduled_for' => '2026-04-12', 'status' => 'direncanakan',
        ])->assertForbidden();
        $this->get(route('farmer.dashboard'))->assertOk()->assertDontSee($url, false)->assertViewHas('activeSeasonCount', 0);
        $this->assertDatabaseCount('activities', 0);
    }

    public function test_dashboard_access_rules_still_apply(): void
    {
        $this->get(route('farmer.dashboard'))->assertRedirect(route('login'));
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)->get(route('farmer.dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs(User::factory()->inactive()->create())->get(route('farmer.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
