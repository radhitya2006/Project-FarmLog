<?php

namespace App\Http\Controllers\Farmer;

use App\Enums\PlantingSeasonStatus;
use App\Http\Controllers\Controller;
use App\Models\PlantingSeason;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $landCount = $request->user()->lands()->count();
        $activeSeasons = PlantingSeason::query()
            ->whereHas('land', fn ($query) => $query->where('user_id', $request->user()->id))
            ->where('status', PlantingSeasonStatus::Active)
            ->with('land:id,name')
            ->orderByDesc('planted_at')->orderByDesc('id')
            ->paginate(6)->fragment('active-seasons');
        $activeSeasonCount = $activeSeasons->total();

        return view('farmer.dashboard.index', compact('landCount', 'activeSeasons', 'activeSeasonCount'));
    }
}
