<?php

namespace Tests\Feature\Farmer;

use App\Enums\UserRole;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LandTest extends TestCase
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
            'name' => 'Sawah Utara',
            'area_hectares' => '1.25',
            'location' => 'Desa Mengwi, Badung',
            'notes' => 'Saluran irigasi di timur.',
        ], $overrides);
    }

    public function test_farmer_can_create_read_update_and_delete_own_land(): void
    {
        $farmer = User::factory()->create();
        $this->actingAs($farmer)->get(route('farmer.dashboard'))->assertOk()
            ->assertSee(route('farmer.lands.index'), false);
        $this->get(route('farmer.lands.create'))->assertOk()->assertSee('Nama lahan');
        $this->post(route('farmer.lands.store'), $this->validData())->assertSessionHasNoErrors();
        $land = $farmer->lands()->sole();
        $this->assertDatabaseHas('lands', ['id' => $land->id, 'user_id' => $farmer->id, 'area_hectares' => '1.25']);
        $this->get(route('farmer.lands.show', $land))->assertOk()->assertSee('Sawah Utara')
            ->assertSee('1,25 hektare')->assertSee('deleteLandModal')->assertSee('Ya, hapus lahan');
        $this->get(route('farmer.lands.edit', $land))->assertOk()->assertSee('value="Sawah Utara"', false);
        $this->put(route('farmer.lands.update', $land), $this->validData(['name' => 'Sawah Selatan', 'notes' => '']))
            ->assertRedirect(route('farmer.lands.show', $land))->assertSessionHas('success');
        $this->assertDatabaseHas('lands', ['id' => $land->id, 'name' => 'Sawah Selatan', 'notes' => null]);
        $this->delete(route('farmer.lands.destroy', $land))->assertRedirect(route('farmer.lands.index'))->assertSessionHas('success');
        $this->assertDatabaseMissing('lands', ['id' => $land->id]);
    }

    public function test_list_search_is_scoped_to_owner_and_counts_seasons(): void
    {
        $farmer = User::factory()->create();
        $land = Land::factory()->for($farmer)->create(['name' => 'Sawah Utara', 'location' => 'Mengwi']);
        PlantingSeason::factory()->count(2)->for($land)->create();
        Land::factory()->for($farmer)->create(['name' => 'Kebun Selatan', 'location' => 'Tabanan']);
        Land::factory()->create(['name' => 'Rahasia Petani Lain', 'location' => 'Mengwi']);

        foreach (['Sawah', 'Mengwi'] as $search) {
            $this->actingAs($farmer)->get(route('farmer.lands.index', ['search' => $search]))
                ->assertOk()->assertDontSee('Rahasia Petani Lain')->assertDontSee('Kebun Selatan')
                ->assertViewHas('lands', fn ($lands) => $lands->total() === 1 && $lands->first()->is($land)
                    && $lands->first()->planting_seasons_count === 2);
        }
    }

    public function test_list_paginates_twelve_lands_in_latest_order_and_preserves_search(): void
    {
        $farmer = User::factory()->create();
        $lands = Land::factory()->count(13)->for($farmer)->create(['location' => 'Mengwi']);
        $this->actingAs($farmer)->get(route('farmer.lands.index', ['search' => 'Mengwi']))->assertOk()
            ->assertSee('Sebelumnya')->assertSee('Berikutnya')->assertSee('dari 13 lahan')
            ->assertViewHas('lands', fn ($page) => $page->count() === 12 && $page->total() === 13
                && $page->first()->is($lands->last()) && str_contains($page->nextPageUrl(), 'search=Mengwi'));
        $this->get(route('farmer.lands.index', ['search' => 'Mengwi', 'page' => 2]))->assertOk()
            ->assertViewHas('lands', fn ($page) => $page->count() === 1 && $page->first()->is($lands->first()));
    }

    public function test_empty_states_and_invalid_search_are_handled(): void
    {
        $farmer = User::factory()->create();
        $this->actingAs($farmer)->get(route('farmer.lands.index'))->assertOk()->assertSee('Mulai dari lahan pertama Anda');
        $this->get(route('farmer.lands.index', ['search' => 'Tidak ada']))->assertOk()->assertSee('Lahan tidak ditemukan');
        $this->get(route('farmer.lands.index', ['search' => ['invalid']]))->assertSessionHasErrors('search');
        $this->get(route('farmer.lands.index', ['search' => str_repeat('a', 151)]))->assertSessionHasErrors('search');
    }

    public function test_other_farmers_cannot_read_edit_update_or_delete_a_land(): void
    {
        $land = Land::factory()->create();
        $this->actingAs(User::factory()->create());
        $this->get(route('farmer.lands.show', $land))->assertForbidden();
        $this->get(route('farmer.lands.edit', $land))->assertForbidden();
        $this->put(route('farmer.lands.update', $land), $this->validData())->assertForbidden();
        $this->delete(route('farmer.lands.destroy', $land))->assertForbidden();
        $this->assertDatabaseHas('lands', ['id' => $land->id, 'name' => $land->name]);
    }

    public function test_owner_cannot_be_supplied_or_changed_even_with_null(): void
    {
        $farmer = User::factory()->create();
        $other = User::factory()->create();
        $land = Land::factory()->for($farmer)->create();
        foreach ([$other->id, null] as $owner) {
            $data = $this->validData(['user_id' => $owner]);
            $this->actingAs($farmer)->post(route('farmer.lands.store'), $data)->assertSessionHasErrors('user_id');
            $this->put(route('farmer.lands.update', $land), $data)->assertSessionHasErrors('user_id');
        }
        $this->assertDatabaseCount('lands', 1);
        $this->assertDatabaseHas('lands', ['id' => $land->id, 'user_id' => $farmer->id, 'name' => $land->name]);
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_fields_are_rejected_on_create_and_update(string $field, mixed $value): void
    {
        $farmer = User::factory()->create();
        $land = Land::factory()->for($farmer)->create();
        $data = $this->validData([$field => $value]);
        $this->actingAs($farmer)->post(route('farmer.lands.store'), $data)->assertSessionHasErrors($field);
        $this->put(route('farmer.lands.update', $land), $data)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('lands', 1);
        $this->assertDatabaseHas('lands', ['id' => $land->id, 'name' => $land->name, 'area_hectares' => $land->area_hectares]);
    }

    public static function invalidFields(): array
    {
        return [
            'missing name' => ['name', ''],
            'long name' => ['name', str_repeat('a', 151)],
            'missing location' => ['location', ''],
            'long location' => ['location', str_repeat('a', 256)],
            'long notes' => ['notes', str_repeat('a', 5001)],
            'notes array' => ['notes', ['invalid']],
            'missing area' => ['area_hectares', ''],
            'zero area' => ['area_hectares', '0'],
            'negative area' => ['area_hectares', '-1'],
            'too precise' => ['area_hectares', '1.001'],
            'too small' => ['area_hectares', '0.001'],
            'too large' => ['area_hectares', '100000000'],
            'not numeric' => ['area_hectares', 'luas'],
            'scientific notation' => ['area_hectares', '1e2'],
        ];
    }

    public function test_boundary_values_optional_notes_and_duplicate_names_are_allowed(): void
    {
        $farmer = User::factory()->create();
        foreach (['0.01', '99999999.99', '1'] as $area) {
            $this->actingAs($farmer)->post(route('farmer.lands.store'), $this->validData([
                'name' => str_repeat('n', 150), 'location' => str_repeat('l', 255),
                'notes' => null, 'area_hectares' => $area,
            ]))->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertDatabaseCount('lands', 3);
    }

    public function test_validation_preserves_form_values_and_renders_errors(): void
    {
        $farmer = User::factory()->create();
        $this->actingAs($farmer)->from(route('farmer.lands.create'))
            ->post(route('farmer.lands.store'), $this->validData(['area_hectares' => '0']))
            ->assertRedirect(route('farmer.lands.create'))->assertSessionHasInput('name', 'Sawah Utara');
        $this->withCookie(config('session.cookie'), session()->getId())->get(route('farmer.lands.create'))
            ->assertOk()->assertSee('value="Sawah Utara"', false)->assertSee('id="area_hectares-error"', false);
    }

    public function test_notes_are_escaped_and_missing_land_returns_404(): void
    {
        $farmer = User::factory()->create();
        $land = Land::factory()->for($farmer)->create(['notes' => '<script>alert(1)</script>']);
        $this->actingAs($farmer)->get(route('farmer.lands.show', $land))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        foreach (['show', 'edit'] as $action) {
            $this->get(route('farmer.lands.'.$action, 999999))->assertNotFound();
        }
        $this->put(route('farmer.lands.update', 999999), $this->validData())->assertNotFound();
        $this->delete(route('farmer.lands.destroy', 999999))->assertNotFound();
    }

    public function test_active_and_finished_seasons_prevent_land_deletion(): void
    {
        $farmer = User::factory()->create();
        foreach (['aktif', 'selesai'] as $status) {
            $land = Land::factory()->for($farmer)->create();
            $season = PlantingSeason::factory()->for($land)->create(['status' => $status]);
            $this->actingAs($farmer)->get(route('farmer.lands.show', $land))->assertOk()
                ->assertSee('Lahan masih memiliki musim tanam')->assertDontSee('id="deleteLandModal"', false);
            $this->delete(route('farmer.lands.destroy', $land))->assertRedirect(route('farmer.lands.show', $land))
                ->assertSessionHas('error');
            $this->assertDatabaseHas('lands', ['id' => $land->id]);
            $this->assertDatabaseHas('planting_seasons', ['id' => $season->id]);
        }
    }

    public function test_foreign_key_race_is_reported_without_deleting_land(): void
    {
        $land = Land::factory()->create();
        Land::deleting(function (Land $deletingLand): void {
            PlantingSeason::factory()->for($deletingLand)->create();
        });
        try {
            $this->actingAs($land->user)->delete(route('farmer.lands.destroy', $land))
                ->assertRedirect(route('farmer.lands.show', $land))->assertSessionHas('error');
            $this->assertDatabaseHas('lands', ['id' => $land->id]);
        } finally {
            Land::flushEventListeners();
        }
    }

    public function test_guests_admins_and_inactive_farmers_cannot_use_land_routes(): void
    {
        $land = Land::factory()->create();
        $endpoints = [
            ['GET', route('farmer.lands.index')], ['GET', route('farmer.lands.create')],
            ['POST', route('farmer.lands.store')], ['GET', route('farmer.lands.show', $land)],
            ['GET', route('farmer.lands.edit', $land)], ['PUT', route('farmer.lands.update', $land)],
            ['DELETE', route('farmer.lands.destroy', $land)],
        ];
        foreach ($endpoints as [$method, $url]) {
            $this->call($method, $url, $this->validData())->assertRedirect(route('login'));
        }
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        foreach ($endpoints as [$method, $url]) {
            $this->actingAs($admin)->call($method, $url, $this->validData())->assertRedirect(route('admin.dashboard'));
        }
        $inactive = User::factory()->inactive()->create();
        foreach ($endpoints as [$method, $url]) {
            $this->actingAs($inactive)->call($method, $url, $this->validData())->assertRedirect(route('login'));
            $this->assertGuest();
        }
        $this->assertDatabaseCount('lands', 1);
    }
}
