<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_dashboard_counts_only_farmers_and_shows_five_recent_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->count(6)->create();
        User::factory()->count(2)->inactive()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('total', 8)->assertViewHas('active', 6)->assertViewHas('inactive', 2)
            ->assertViewHas('recentFarmers', fn ($users) => $users->count() === 5
                && $users->every(fn (User $user) => $user->role === UserRole::Farmer));
    }

    public function test_search_and_status_filters_do_not_expose_admin_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'email' => 'search-admin@example.com']);
        $match = User::factory()->inactive()->create(['email' => 'search-farmer@example.com']);
        User::factory()->create(['email' => 'search-active@example.com']);
        User::factory()->inactive()->create(['email' => 'unmatched@example.com']);

        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'search-', 'status' => 'nonaktif']))
            ->assertOk()->assertViewHas('farmers', fn ($users) => $users->total() === 1 && $users->first()->is($match));
        $this->get(route('admin.users.index', ['search' => 'search-admin']))
            ->assertOk()->assertSee('Petani tidak ditemukan');
    }

    public function test_account_list_is_paginated_and_preserves_filters(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        User::factory()->count(12)->create();

        $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'aktif']))
            ->assertOk()->assertViewHas('farmers', fn ($users) => $users->count() === 10
                && $users->total() === 12 && str_contains($users->nextPageUrl(), 'status=aktif'));
    }

    public function test_details_only_accept_farmer_accounts_and_do_not_expose_passwords(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.users.show', $farmer))
            ->assertOk()->assertSee($farmer->email)->assertDontSee($farmer->password);
        $this->get(route('admin.users.show', $admin))->assertForbidden();
        $this->get(route('admin.users.show', 999999))->assertNotFound();
    }

    public function test_create_edit_update_and_delete_account_routes_are_unavailable(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();
        $this->actingAs($admin)->post('/admin/users', ['name' => 'New farmer'])->assertMethodNotAllowed();
        $this->get('/admin/users/create')->assertNotFound();
        $this->get('/admin/users/'.$farmer->id.'/edit')->assertNotFound();
        $this->patch('/admin/users/'.$farmer->id, ['name' => 'Changed'])->assertMethodNotAllowed();
        $this->delete('/admin/users/'.$farmer->id)->assertMethodNotAllowed();
        $this->assertDatabaseCount('users', 2);
        $this->assertTrue($farmer->fresh()->is_active);
    }

    public function test_guest_farmer_and_inactive_admin_cannot_use_admin_pages(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        $farmer = User::factory()->create();
        $this->actingAs($farmer);
        foreach (['/admin', '/admin/users', '/admin/users/'.$farmer->id] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->patch(route('admin.users.status', $farmer), ['is_active' => false])->assertForbidden();
        $this->assertTrue($farmer->fresh()->is_active);
        $this->actingAs(User::factory()->inactive()->create(['role' => UserRole::Admin]))
            ->patch(route('admin.users.status', $farmer), ['is_active' => false])->assertRedirect(route('login'));
        $this->assertGuest();
        $this->patch(route('admin.users.status', $farmer), ['is_active' => false])->assertRedirect(route('login'));
        $this->assertTrue($farmer->fresh()->is_active);
    }

    public function test_admin_can_change_status_and_repeated_requests_are_idempotent(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();
        $this->actingAs($admin);

        foreach ([false, false, true] as $isActive) {
            $this->patch(route('admin.users.status', $farmer), ['is_active' => $isActive])
                ->assertRedirect(route('admin.users.show', $farmer))->assertSessionHas('success');
            $this->assertSame($isActive, $farmer->fresh()->is_active);
        }
    }

    public function test_status_requests_cannot_target_any_admin_or_missing_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $otherAdmin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        foreach ([$admin, $otherAdmin] as $target) {
            $this->patch(route('admin.users.status', $target), ['is_active' => false])->assertForbidden();
            $this->assertTrue($target->fresh()->is_active);
        }
        $this->patch(route('admin.users.status', 999999), ['is_active' => false])->assertNotFound();
    }

    public function test_status_requires_a_boolean_and_does_not_accept_identity_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();
        $before = $farmer->fresh()->getAttributes();
        $this->actingAs($admin);

        foreach ([[], ['is_active' => null], ['is_active' => 'nonaktif'], ['is_active' => ['0']]] as $payload) {
            $this->from(route('admin.users.show', $farmer))
                ->patch(route('admin.users.status', $farmer), $payload)->assertSessionHasErrors('is_active');
        }
        foreach ([
            'name' => 'Changed name', 'email' => 'changed@example.com', 'password' => 'changed-password',
            'password_confirmation' => 'changed-password', 'role' => 'admin',
        ] as $field => $value) {
            $this->patch(route('admin.users.status', $farmer), ['is_active' => false, $field => $value])
                ->assertSessionHasErrors($field);
        }
        // Even empty identity fields must not be accepted by the status endpoint.
        $this->patch(route('admin.users.status', $farmer), ['is_active' => false, 'email' => null])
            ->assertSessionHasErrors('email');
        $this->assertSame($before, $farmer->fresh()->getAttributes());
    }

    public function test_deactivation_preserves_identity_and_all_farming_history(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();
        $identity = $farmer->only(['name', 'email', 'password', 'role']);
        $land = Land::factory()->for($farmer)->create();
        $season = PlantingSeason::factory()->for($land)->create();
        $activity = Activity::factory()->for($season)->create();
        $harvest = Harvest::factory()->for($season)->create();
        $history = [$land->fresh(), $season->fresh(), $activity->fresh(), $harvest->fresh()];

        $this->actingAs($admin)->patch(route('admin.users.status', $farmer), ['is_active' => '0'])
            ->assertRedirect(route('admin.users.show', $farmer));
        $this->assertSame($identity, $farmer->fresh()->only(['name', 'email', 'password', 'role']));
        foreach ($history as $record) {
            $this->assertModelExists($record);
            $this->assertSame($record->getAttributes(), $record->fresh()->getAttributes());
        }
    }

    public function test_deactivated_farmer_loses_session_and_can_login_after_reactivation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();
        $this->actingAs($admin)->patch(route('admin.users.status', $farmer), ['is_active' => false]);

        // Emulate the farmer's next request, reloading their persisted account status.
        $this->actingAs($farmer->fresh())->withSession(['private_data' => 'must disappear']);
        $this->get(route('farmer.dashboard'))->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')->assertSessionMissing('private_data');
        $this->assertGuest();
        $this->post(route('login.store'), ['email' => $farmer->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($admin)->patch(route('admin.users.status', $farmer), ['is_active' => true]);
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => $farmer->email, 'password' => 'password'])
            ->assertRedirect(route('farmer.dashboard'));
        $this->assertAuthenticatedAs($farmer);
    }
}
