<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\HarvestUnit;
use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_follow_relationships_and_column_casts(): void
    {
        $farmer = User::factory()->create(['email' => '  PETANI @EXAMPLE.COM  ']);
        $land = Land::factory()->for($farmer)->create(['area_hectares' => '1.50']);
        $season = PlantingSeason::factory()->for($land)->create();
        $activity = Activity::factory()->for($season)->create();
        $harvest = Harvest::factory()->for($season)->create(['quantity' => '125.50']);

        $this->assertSame('petani@example.com', $farmer->email);
        $this->assertSame(UserRole::Farmer, $farmer->role);
        $this->assertTrue($farmer->is_active);
        $this->assertSame('1.50', $land->area_hectares);
        $this->assertSame('125.50', $harvest->quantity);
        $this->assertSame(PlantingSeasonStatus::Active, $season->status);
        $this->assertSame(ActivityStatus::Planned, $activity->status);
        $this->assertSame(HarvestUnit::Kilogram, $harvest->unit);
        $this->assertTrue($farmer->lands->first()->is($land));
        $this->assertTrue($land->plantingSeasons->first()->is($season));
        $this->assertTrue($season->activities->first()->is($activity));
        $this->assertTrue($season->harvests->first()->is($harvest));
        $this->assertTrue($harvest->plantingSeason->is($season));
    }

    public function test_a_land_with_a_planting_season_cannot_be_deleted(): void
    {
        $land = Land::factory()->create();
        PlantingSeason::factory()->for($land)->create();

        try {
            $land->delete();
            $this->fail('A land with planting seasons must be protected by its foreign key.');
        } catch (QueryException) {
            $this->assertDatabaseHas('lands', ['id' => $land->id]);
        }
    }

    public function test_deleting_a_season_cascades_to_activities_and_harvests(): void
    {
        $season = PlantingSeason::factory()->create();
        $activity = Activity::factory()->for($season)->create();
        $harvest = Harvest::factory()->for($season)->create();

        $season->delete();

        $this->assertDatabaseMissing('planting_seasons', ['id' => $season->id]);
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
        $this->assertDatabaseMissing('harvests', ['id' => $harvest->id]);
    }

    public function test_seeder_creates_one_admin_and_repeatable_demo_data(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('lands', 1);
        $this->assertDatabaseCount('planting_seasons', 1);
        $this->assertDatabaseCount('activities', 2);
        $this->assertDatabaseCount('harvests', 1);
        $this->assertSame(UserRole::Admin, User::query()->where('email', 'admin@farmlog.test')->firstOrFail()->role);
    }
}
