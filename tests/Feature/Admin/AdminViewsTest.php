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

    public function test_create_preview_has_no_submittable_account_fields(): void
    {
        $this->get(route('admin.users.create'))->assertOk()
            ->assertSee('Pratinjau pengelolaan akun')
            ->assertSee('disabled aria-describedby="save-preview-help"', false)
            ->assertDontSee('name="password"', false)
            ->assertDontSee('name="role"', false)
            ->assertDontSee('name="email"', false);
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

    public function test_list_escapes_names_and_marks_status_actions_as_preview(): void
    {
        $farmer = User::factory()->make(['id' => 2, 'name' => '<script>alert(1)</script>']);
        $this->view('admin.users.index', [
            'farmers' => new LengthAwarePaginator([$farmer], 1, 10), 'search' => '', 'status' => '',
        ])->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Pratinjau saja. Status akun belum dapat diubah.')
            ->assertSee('disabled data-status-confirm', false);
    }

    public function test_edit_preview_does_not_expose_password_or_role_controls(): void
    {
        $farmer = User::factory()->make(['id' => 2, 'is_active' => false]);
        $this->view('admin.users.edit', ['farmer' => $farmer])
            ->assertSee($farmer->email)->assertSee('Nonaktif')
            ->assertDontSee($farmer->password)->assertDontSee('id="farmer-password"', false)
            ->assertDontSee('name="role"', false);
    }
}
