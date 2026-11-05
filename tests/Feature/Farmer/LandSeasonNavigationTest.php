<?php

namespace Tests\Feature\Farmer;

use App\Models\Land;
use App\Models\PlantingSeason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandSeasonNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_season_pages_keep_land_navigation_active_and_only_link_to_land_context(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $this->actingAs($land->user)->get(route('farmer.lands.show', $land))->assertOk()
            ->assertSee(route('farmer.lands.planting-seasons.index', $land), false)
            ->assertSee(route('farmer.lands.planting-seasons.create', $land), false);

        foreach (['index', 'create', 'show', 'edit'] as $action) {
            $parameters = in_array($action, ['index', 'create']) ? [$land] : [$land, $season];
            $this->get(route('farmer.lands.planting-seasons.'.$action, $parameters))->assertOk()
                ->assertSee('href="'.route('farmer.lands.index').'" class="is-active"', false)
                ->assertDontSee(url('/planting-seasons'), false);
        }
        $this->get(route('farmer.lands.planting-seasons.index', $land))->assertOk()
            ->assertSee(route('farmer.lands.show', $land), false)->assertDontSee('name="land_id"', false);
        $this->get(route('farmer.lands.planting-seasons.show', [$land, $season]))->assertOk()
            ->assertSee(route('farmer.lands.planting-seasons.index', $land), false);
    }

    public function test_query_parameters_cannot_switch_the_land_in_the_url(): void
    {
        $land = Land::factory()->create();
        $season = PlantingSeason::factory()->for($land)->create(['crop_name' => 'Padi Utara']);
        $otherLand = Land::factory()->for($land->user)->create();
        PlantingSeason::factory()->for($otherLand)->create(['crop_name' => 'Jagung Selatan']);
        $this->actingAs($land->user)->get(route('farmer.lands.planting-seasons.index', [
            'land' => $land, 'land_id' => $otherLand->id,
        ]))->assertOk()->assertSee('Padi Utara')->assertDontSee('Jagung Selatan')
            ->assertViewHas('seasons', fn ($page) => $page->total() === 1 && $page->first()->is($season));
    }

    public function test_standalone_season_pages_are_no_longer_available(): void
    {
        foreach (['/planting-seasons', '/planting-seasons/create', '/planting-seasons/select-land'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }
}
