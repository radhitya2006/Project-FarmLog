<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'search.string' => 'Pencarian harus berupa teks.',
            'search.max' => 'Pencarian maksimal 150 karakter.',
            'status.in' => 'Pilih status akun yang tersedia.',
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';

        $farmers = User::query()->where('role', UserRole::Farmer)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'aktif'))
            ->latest('id')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('farmers', 'search', 'status'));
    }

    public function show(User $user): View
    {
        abort_unless($user->role === UserRole::Farmer, 403);

        return view('admin.users.show', ['farmer' => $user]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        $isActive = (bool) $request->validated('is_active');

        if ($user->is_active !== $isActive) {
            $user->is_active = $isActive;
            $user->save();
        }

        return to_route('admin.users.show', $user)->with('success', $isActive
            ? 'Akses akun petani aktif. Petani dapat masuk ke FarmLog.'
            : 'Akses akun petani dinonaktifkan. Riwayat pertanian tetap tersimpan.');
    }
}
