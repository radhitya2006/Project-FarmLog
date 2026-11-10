<?php

namespace Tests\Feature\Farmer;

use App\Models\Activity;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummariesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_pending_activities_include_read_only_seasons_and_exclude_completed_or_foreign_records(): void
    {
        $season = PlantingSeason::factory()->create();
        $finished = PlantingSeason::factory()->finished()->for($season->land)->create();
        $oldest = Activity::factory()->for($finished)->create(['name' => 'Persiapan lama', 'scheduled_for' => '2026-01-01']);
        Activity::factory()->for($season)->create(['name' => 'Pupuk terjadwal', 'scheduled_for' => '2026-04-01']);
        Activity::factory()->finished()->for($season)->create(['name' => 'Sudah dikerjakan']);
        Activity::factory()->create(['name' => 'Kegiatan petani lain']);

        $this->actingAs($season->land->user)->get(route('farmer.dashboard'))->assertOk()
            ->assertViewHas('pendingActivityCount', 2)
            ->assertViewHas('pendingActivities', fn ($page) => $page->total() === 2 && $page->first()->is($oldest)
                && $page->every(fn ($activity) => $activity->relationLoaded('plantingSeason') && $activity->plantingSeason->relationLoaded('land')))
            ->assertSee('Persiapan lama')->assertSee('Pupuk terjadwal')->assertDontSee('Sudah dikerjakan')->assertDontSee('Kegiatan petani lain')
            ->assertSee(route('farmer.lands.planting-seasons.activities.show', [$season->land, $finished, $oldest]), false);
    }

    public function test_harvest_summaries_are_per_season_and_unit_including_finished_seasons(): void
    {
        $season = PlantingSeason::factory()->create(['crop_name' => 'Padi Ringkasan']);
        $finished = PlantingSeason::factory()->finished()->for($season->land)->create(['crop_name' => 'Cabai Riwayat']);
        Harvest::factory()->for($season)->create(['quantity' => '100.10', 'unit' => 'kg']);
        Harvest::factory()->for($season)->create(['quantity' => '50.20', 'unit' => 'kg']);
        Harvest::factory()->for($season)->create(['quantity' => '2.25', 'unit' => 'ton']);
        Harvest::factory()->for($finished)->create(['quantity' => '10.50', 'unit' => 'kg']);
        Harvest::factory()->for(PlantingSeason::factory()->create(['crop_name' => 'Panen petani lain']))->create(['quantity' => '999']);

        $this->actingAs($season->land->user)->get(route('farmer.dashboard'))->assertOk()
            ->assertViewHas('harvestSeasons', fn ($page) => $page->total() === 2
                && $page->firstWhere('id', $season->id)->total_kg === '150.30'
                && $page->firstWhere('id', $season->id)->total_ton === '2.25')
            ->assertSee('150,30 kg')->assertSee('2,25 ton')->assertSee('10,50 kg')->assertSee('Cabai Riwayat')->assertDontSee('Panen petani lain')
            ->assertSee(route('farmer.lands.planting-seasons.harvests.index', [$season->land, $finished]), false)
            ->assertSee(route('farmer.lands.planting-seasons.harvests.create', [$season->land, $season]), false)
            ->assertDontSee(route('farmer.lands.planting-seasons.harvests.create', [$season->land, $finished]), false);
    }

    public function test_dashboard_lists_have_independent_pagination_and_preserve_other_pages(): void
    {
        $farmer = User::factory()->create();
        $land = Land::factory()->for($farmer)->create();
        $seasons = PlantingSeason::factory()->count(7)->for($land)->create(['planted_at' => '2026-01-01']);
        foreach ($seasons as $season) {
            Activity::factory()->for($season)->create(['scheduled_for' => '2026-04-01']);
            Harvest::factory()->for($season)->create(['quantity' => '1.25']);
        }
        $this->actingAs($farmer)->get(route('farmer.dashboard', ['page' => 2, 'activities_page' => 1, 'harvest_page' => 2]))->assertOk()
            ->assertViewHas('activeSeasons', fn ($page) => $page->count() === 1 && $page->total() === 7)
            ->assertViewHas('pendingActivities', fn ($page) => $page->count() === 6 && $page->total() === 7
                && str_contains($page->nextPageUrl(), 'page=2') && str_contains($page->nextPageUrl(), 'harvest_page=2')
                && str_ends_with($page->nextPageUrl(), 'activities_page=2#pending-activities'))
            ->assertViewHas('harvestSeasons', fn ($page) => $page->count() === 1 && $page->total() === 7);
    }

    public function test_dashboard_has_empty_states_and_out_of_range_recovery_for_both_lists(): void
    {
        $farmer = User::factory()->create();
        $this->actingAs($farmer)->get(route('farmer.dashboard'))->assertOk()
            ->assertSee('Tidak ada kegiatan tertunda')->assertSee('Belum ada hasil panen');
        $season = PlantingSeason::factory()->for(Land::factory()->for($farmer))->create();
        Activity::factory()->for($season)->create();
        Harvest::factory()->for($season)->create();
        $this->get(route('farmer.dashboard', ['activities_page' => 99, 'harvest_page' => 99]))->assertOk()
            ->assertSee('Tidak ada kegiatan di halaman ini')->assertSee('Tidak ada ringkasan di halaman ini');
    }
}
