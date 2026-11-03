<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
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

    public function test_edit_preview_only_accepts_farmer_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.users.edit', $farmer))
            ->assertOk()->assertSee($farmer->email)->assertDontSee($farmer->password);
        $this->get(route('admin.users.edit', $admin))->assertForbidden();
        $this->get(route('admin.users.edit', 999999))->assertNotFound();
    }

    public function test_preview_routes_cannot_modify_or_delete_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $farmer = User::factory()->create();
        $this->actingAs($admin)->post('/admin/users', ['name' => 'New farmer'])->assertMethodNotAllowed();
        $this->patch('/admin/users/'.$farmer->id, ['is_active' => false])->assertNotFound();
        $this->delete('/admin/users/'.$farmer->id)->assertNotFound();
        $this->assertDatabaseCount('users', 2);
        $this->assertTrue($farmer->fresh()->is_active);
    }

    public function test_guest_farmer_and_inactive_admin_cannot_use_admin_pages(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        $farmer = User::factory()->create();
        $this->actingAs($farmer);
        foreach (['/admin', '/admin/users', '/admin/users/create', '/admin/users/'.$farmer->id.'/edit'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->actingAs(User::factory()->inactive()->create(['role' => UserRole::Admin]))
            ->get('/admin/users')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
