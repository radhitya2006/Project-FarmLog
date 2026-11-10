<?php

namespace Tests\Feature\Farmer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmLogFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_through_harvest_and_season_lifecycle(): void
    {
        $this->withoutVite();
        $this->post(route('register.store'), [
            'name' => 'Petani Demo', 'email' => 'MVP@example.com',
            'password' => 'ExamplePass123!', 'password_confirmation' => 'ExamplePass123!',
        ])->assertRedirect(route('login'))->assertSessionHasNoErrors();
        $this->assertGuest();
        $this->post(route('login.store'), ['email' => 'mvp@example.com', 'password' => 'ExamplePass123!'])
            ->assertRedirect(route('farmer.dashboard'));
        $farmer = User::where('email', 'mvp@example.com')->sole();
        $this->post(route('farmer.lands.store'), ['name' => 'Sawah MVP', 'area_hectares' => '1.50', 'location' => 'Bali'])
            ->assertSessionHasNoErrors();
        $land = $farmer->lands()->sole();
        $this->post(route('farmer.lands.planting-seasons.store', $land), [
            'crop_name' => 'Padi', 'planted_at' => '2026-01-10', 'expected_harvest_at' => '2026-04-10',
        ])->assertSessionHasNoErrors();
        $season = $land->plantingSeasons()->sole();
        $this->post(route('farmer.lands.planting-seasons.activities.store', [$land, $season]), [
            'name' => 'Pemupukan', 'scheduled_for' => '2026-01-15', 'status' => 'direncanakan',
        ])->assertSessionHasNoErrors();
        $activity = $season->activities()->sole();
        $this->patch(route('farmer.lands.planting-seasons.activities.finish', [$land, $season, $activity]))->assertRedirect();
        foreach ([['100.25', 'kg'], ['49.75', 'kg'], ['0.50', 'ton']] as [$quantity, $unit]) {
            $this->post(route('farmer.lands.planting-seasons.harvests.store', [$land, $season]), [
                'harvested_at' => '2026-04-10', 'quantity' => $quantity, 'unit' => $unit,
            ])->assertSessionHasNoErrors()->assertRedirect();
        }
        $harvestIds = $season->harvests()->pluck('id');
        $this->get(route('farmer.dashboard'))->assertOk()->assertSee('150,00 kg')->assertSee('0,50 ton')
            ->assertViewHas('pendingActivityCount', 0);
        $this->patch(route('farmer.lands.planting-seasons.finish', [$land, $season]), ['confirmed' => 1])->assertRedirect();
        $this->get(route('farmer.lands.planting-seasons.harvests.create', [$land, $season]))->assertForbidden();
        $this->get(route('farmer.dashboard'))->assertOk()->assertViewHas('activeSeasonCount', 0)->assertSee('150,00 kg');
        $this->patch(route('farmer.lands.planting-seasons.reopen', [$land, $season]), ['confirmed' => 1])->assertRedirect();
        $this->get(route('farmer.lands.planting-seasons.harvests.edit', [$land, $season, $harvestIds->first()]))->assertOk();
        $this->delete(route('farmer.lands.planting-seasons.destroy', [$land, $season]), ['confirmed' => 1])->assertRedirect();
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
        foreach ($harvestIds as $id) {
            $this->assertDatabaseMissing('harvests', ['id' => $id]);
        }
        $this->delete(route('farmer.lands.destroy', $land))->assertRedirect(route('farmer.lands.index'));
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
