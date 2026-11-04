<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class AdminViewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Direct view rendering does not run ShareErrorsFromSession middleware.
        view()->share('errors', new ViewErrorBag);
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => UserRole::Admin]));
    }

    public function test_empty_dashboard_and_list_explain_missing_data(): void
    {
        $this->view('admin.dashboard', [
            'total' => 0, 'active' => 0, 'inactive' => 0, 'recentFarmers' => collect(),
        ])->assertSee('Belum ada akun petani');

        $this->view('admin.users.index', [
            'farmers' => new LengthAwarePaginator([], 0, 10), 'search' => 'missing', 'status' => '',
        ])->assertSee('Petani tidak ditemukan')->assertSee('Tampilkan semua petani');
    }

    public function test_list_escapes_names_and_only_submits_status_from_the_confirmation_dialog(): void
    {
        $farmer = User::factory()->make(['id' => 2, 'name' => '<script>alert(1)</script>']);
        $this->view('admin.users.index', [
            'farmers' => new LengthAwarePaginator([$farmer], 1, 10), 'search' => '', 'status' => '',
        ])->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee(route('admin.users.show', $farmer), false)
            ->assertSee(route('admin.users.status', $farmer), false)
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('disabled data-status-confirm', false)
            ->assertDontSee('Tambah petani')->assertDontSee('Edit akun');
    }

    public function test_account_details_are_read_only_and_do_not_expose_passwords(): void
    {
        $farmer = User::factory()->make(['id' => 2, 'is_active' => false]);
        $this->view('admin.users.show', ['farmer' => $farmer])
            ->assertSee($farmer->email)->assertSee('Nonaktif')
            ->assertSee('Aktifkan akses')->assertDontSee($farmer->password)
            ->assertDontSee('name="name"', false)->assertDontSee('name="email"', false)
            ->assertDontSee('name="password"', false)->assertDontSee('name="role"', false);
    }
}
