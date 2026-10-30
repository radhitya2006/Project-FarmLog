<?php

namespace Tests\Feature\Auth;

use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OwnershipPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_resources_but_other_farmer_admin_and_inactive_user_cannot(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $land = Land::factory()->for($owner)->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $activity = Activity::factory()->for($season)->create();
        $harvest = Harvest::factory()->for($season)->create();

        foreach ([$land, $season, $activity, $harvest] as $resource) {
            foreach (['view', 'update', 'delete'] as $ability) {
                $this->assertTrue(Gate::forUser($owner)->allows($ability, $resource));
                $this->assertFalse(Gate::forUser($other)->allows($ability, $resource));
                $this->assertFalse(Gate::forUser($admin)->allows($ability, $resource));
            }
        }

        $this->assertTrue(Gate::forUser($owner)->allows('create', [PlantingSeason::class, $land]));
        $this->assertFalse(Gate::forUser($other)->allows('create', [PlantingSeason::class, $land]));
        foreach ([Activity::class, Harvest::class] as $class) {
            $this->assertTrue(Gate::forUser($owner)->allows('create', [$class, $season]));
            $this->assertFalse(Gate::forUser($other)->allows('create', [$class, $season]));
        }

        $owner->is_active = false;
        foreach ([$land, $season, $activity, $harvest] as $resource) {
            $this->assertFalse(Gate::forUser($owner)->allows('view', $resource));
        }
    }

    public function test_finished_season_children_are_read_only_until_reopened(): void
    {
        $season = PlantingSeason::factory()->finished()->create();
        $owner = $season->land->user;
        $activity = Activity::factory()->for($season)->create();
        $harvest = Harvest::factory()->for($season)->create();

        foreach ([$activity, $harvest] as $resource) {
            $this->assertTrue(Gate::forUser($owner)->allows('view', $resource));
            $this->assertFalse(Gate::forUser($owner)->allows('update', $resource));
            $this->assertFalse(Gate::forUser($owner)->allows('delete', $resource));
            $this->assertFalse(Gate::forUser($owner)->allows('create', [$resource::class, $season]));
        }
        $this->assertTrue(Gate::forUser($owner)->allows('reopen', $season));
        $season->update(['status' => PlantingSeasonStatus::Active]);
        foreach ([$activity, $harvest] as $resource) {
            $this->assertTrue(Gate::forUser($owner)->allows('update', $resource));
            $this->assertTrue(Gate::forUser($owner)->allows('create', [$resource::class, $season]));
        }
    }

    public function test_policy_denial_becomes_http_403(): void
    {
        $this->withoutVite();
        $land = Land::factory()->create();
        Route::get('/_test/lands/{land}', function (Land $land) {
            Gate::authorize('view', $land);

            return response('Diizinkan');
        })->middleware('web');

        $this->actingAs(User::factory()->create())->get('/_test/lands/'.$land->id)->assertForbidden();
        $this->actingAs($land->user)->get('/_test/lands/'.$land->id)->assertOk();
    }
}
