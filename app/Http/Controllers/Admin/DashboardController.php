<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $farmers = User::query()->where('role', UserRole::Farmer);
        $total = (clone $farmers)->count();
        $active = (clone $farmers)->where('is_active', true)->count();

        return view('admin.dashboard', [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'recentFarmers' => $farmers->latest('id')->limit(5)->get(),
        ]);
    }
}
