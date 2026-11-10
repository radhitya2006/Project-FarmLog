<?php

namespace App\Http\Controllers\Farmer;

use App\Enums\ActivityStatus;
use App\Enums\PlantingSeasonStatus;
use App\Http\Controllers\Controller;
use App\Models\Activity;
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
            ->paginate(6)->withQueryString()->fragment('active-seasons');
        $activeSeasonCount = $activeSeasons->total();
        $pendingActivities = Activity::query()->where('status', ActivityStatus::Planned)
            ->whereHas('plantingSeason.land', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['plantingSeason:id,land_id,crop_name,planted_at,status', 'plantingSeason.land:id,name'])
            ->orderBy('scheduled_for')->orderBy('id')
            ->paginate(6, ['*'], 'activities_page')->withQueryString()->fragment('pending-activities');
        $pendingActivityCount = $pendingActivities->total();
        $harvestSeasons = PlantingSeason::query()
            ->whereHas('land', fn ($query) => $query->where('user_id', $request->user()->id))
            ->whereHas('harvests')->with('land:id,name')->withHarvestTotals()
            ->orderByDesc('planted_at')->orderByDesc('id')
            ->paginate(6, ['*'], 'harvest_page')->withQueryString()->fragment('harvest-summary');

        return view('farmer.dashboard.index', compact('landCount', 'activeSeasons', 'activeSeasonCount', 'pendingActivities', 'pendingActivityCount', 'harvestSeasons'));
    }
}
