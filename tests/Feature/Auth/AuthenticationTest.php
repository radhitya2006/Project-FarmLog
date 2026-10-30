<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Petani Baru',
            'email' => 'petani@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_guest_pages_render_and_internal_pages_require_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/login')->assertOk()->assertSee('Selamat datang kembali')->assertDontSee('Lupa kata sandi');
        $this->get('/register')->assertOk()->assertSee('Buat akun');
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_registration_normalizes_email_forces_farmer_and_flashes_email_without_login(): void
    {
        $this->post('/register', $this->registration([
            'email' => "  PETANI @EXAMPLE.COM\t",
            'role' => 'admin',
            'is_active' => false,
        ]))->assertRedirect(route('login'))
            ->assertSessionHas('email', 'petani@example.com')
            ->assertSessionHas('success')
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
        $user = User::query()->sole();
        $this->assertSame(UserRole::Farmer, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->get('/login')->assertSee('value="petani@example.com"', false);
    }

    public function test_registration_rejects_duplicate_normalized_email(): void
    {
        User::factory()->create(['email' => 'petani@example.com']);
        $this->post('/register', $this->registration(['email' => ' PETANI @EXAMPLE.COM ']))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_rejects_short_or_mismatched_passwords(): void
    {
        $this->post('/register', $this->registration(['password' => 'short', 'password_confirmation' => 'short']))
            ->assertSessionHasErrors('password');
        $this->post('/register', $this->registration(['password_confirmation' => 'different']))
            ->assertSessionHasErrors('password');
        $this->post('/register', $this->registration(['email' => ['invalid']]))
            ->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_farmer_login_normalizes_email_regenerates_session_and_ignores_intended_admin_url(): void
    {
        $user = User::factory()->create(['email' => 'petani@example.com']);
        $this->withSession(['url.intended' => '/admin']);
        $oldId = session()->getId();
        $this->post('/login', ['email' => ' PETANI @EXAMPLE.COM ', 'password' => 'password'])
            ->assertRedirect(route('farmer.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldId, session()->getId());
        $this->get('/dashboard')->assertOk();
    }

    public function test_oversized_unicode_password_is_rejected_without_hashing_error(): void
    {
        $password = str_repeat('é', 40);
        $this->post('/register', $this->registration([
            'password' => $password,
            'password_confirmation' => $password,
        ]))->assertSessionHasErrors('password');
        $this->post('/login', ['email' => 'petani@example.com', 'password' => $password])
            ->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_inactive_session_is_cleared_even_on_guest_routes(): void
    {
        $this->actingAs(User::factory()->inactive()->create())
            ->get('/login')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/login')->assertOk();
    }

    public function test_admin_login_and_role_redirects(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->get('/admin')->assertOk();
        $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->get('/register')->assertRedirect(route('admin.dashboard'));
    }

    public function test_farmer_is_forbidden_from_admin_and_redirected_from_guest_routes(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin')->assertForbidden();
        $this->get('/login')->assertRedirect(route('farmer.dashboard'));
        $this->get('/register')->assertRedirect(route('farmer.dashboard'));
    }

    public function test_wrong_password_and_inactive_account_cannot_login(): void
    {
        $active = User::factory()->create();
        $inactive = User::factory()->inactive()->create();

        foreach ([
            ['email' => $active->email, 'password' => 'wrong'],
            ['email' => $inactive->email, 'password' => 'password'],
        ] as $credentials) {
            $this->post('/login', $credentials)->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }

    public function test_deactivated_account_loses_session_on_next_request(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->withSession(['private_data' => 'must disappear']);
        User::query()->whereKey($user->id)->update(['is_active' => false]);
        // Simulate a new HTTP request reloading the user from the database.
        $this->app['auth']->forgetGuards();
        $this->get('/dashboard')->assertRedirect(route('login'))->assertSessionHasErrors('email')
            ->assertSessionMissing('private_data');
        $this->assertGuest();
    }

    public function test_logout_invalidates_session_and_rotates_csrf_token(): void
    {
        $this->actingAs(User::factory()->create())->withSession(['private_data' => 'secret']);
        session()->regenerateToken();
        $oldToken = session()->token();
        $oldId = session()->getId();
        $this->post('/logout')->assertRedirect(route('login'))->assertSessionMissing('private_data');
        $this->assertGuest();
        $this->assertNotSame($oldToken, session()->token());
        $this->assertNotSame($oldId, session()->getId());
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/logout')->assertMethodNotAllowed();
    }

    public function test_login_is_limited_to_five_attempts_per_minute_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => "unknown{$i}@example.com", 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'another@example.com', 'password' => 'wrong'])
            ->assertStatus(429)->assertHeader('Retry-After')->assertSee('Terlalu banyak percobaan');
        $this->travel(61)->seconds();
        $this->post('/login', ['email' => 'another@example.com', 'password' => 'wrong'])
            ->assertStatus(302);
    }

    public function test_registration_is_limited_to_three_attempts_per_hour_per_ip(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', [])->assertSessionHasErrors('email');
        }
        $this->post('/register', $this->registration())->assertStatus(429);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->post('/register', $this->registration())->assertRedirect(route('login'));
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);
        $this->travel(3601)->seconds();
        $this->post('/register', $this->registration(['email' => 'next@example.com']))
            ->assertRedirect(route('login'));
    }
}
