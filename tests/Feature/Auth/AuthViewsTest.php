<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class AuthViewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_authentication_pages_render_accessible_fields_and_navigation(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('Selamat datang kembali')
            ->assertSee('aria-label="Tampilkan kata sandi"', false)
            ->assertSee('data-submit-label', false)
            ->assertSee(route('register'), false);

        $this->get('/register')->assertOk()
            ->assertSee('Gunakan minimal 8 karakter.')
            ->assertSee('id="password-help"', false)
            ->assertSee('Konfirmasi kata sandi')
            ->assertSee(route('login'), false);
    }

    public function test_error_fields_preserve_email_but_never_repopulate_password(): void
    {
        $password = str_repeat('NeverRenderThisPassword', 4);
        $this->from('/login')->post('/login', [
            'email' => 'petani@example.com',
            'password' => $password,
        ])->assertSessionHasErrors('password');

        $this->withCookie(config('session.cookie'), session()->getId())
            ->get('/login')->assertOk()
            ->assertSee('value="petani@example.com"', false)
            ->assertSee('id="password-error"', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('Kata sandi terlalu panjang.')
            ->assertDontSee($password);
    }

    public function test_registration_success_is_announced_and_email_prefilled_on_login(): void
    {
        $this->withSession([
            'email' => 'petani@example.com',
            'success' => 'Akun berhasil dibuat. Silakan masuk.',
        ])->get('/login')->assertOk()
            ->assertSee('role="status"', false)
            ->assertSee('Akun berhasil dibuat. Silakan masuk.')
            ->assertSee('value="petani@example.com"', false);
    }
}
