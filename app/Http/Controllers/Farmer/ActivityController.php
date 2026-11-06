<?php

namespace App\Http\Controllers\Farmer;

use App\Enums\ActivityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\ActivityActionRequest;
use App\Http\Requests\Farmer\ActivityRequest;
use App\Models\Activity;
use App\Models\Land;
use App\Models\PlantingSeason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request, Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('view', $plantingSeason);
        Gate::authorize('viewAny', Activity::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::enum(ActivityStatus::class)],
        ], [
            'search.string' => 'Pencarian harus berupa teks.',
            'search.max' => 'Pencarian maksimal 150 karakter.',
            'status.enum' => 'Pilih status kegiatan yang tersedia.',
        ]);
        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';
        $activities = $plantingSeason->activities()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('scheduled_for')->orderByDesc('id')->paginate(12)->withQueryString();

        return view('farmer.activities.index', ['land' => $land, 'season' => $plantingSeason,
            'activities' => $activities, 'search' => $search, 'status' => $status]);
    }

    public function create(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('create', [Activity::class, $plantingSeason]);

        return view('farmer.activities.create', ['land' => $land, 'season' => $plantingSeason, 'activity' => new Activity]);
    }

    public function store(ActivityRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        $activity = DB::transaction(function () use ($request, $land, $plantingSeason): Activity {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('create', [Activity::class, $season]);

            return $season->activities()->create($request->safe()->only(['name', 'scheduled_for', 'status', 'notes']));
        });

        return to_route('farmer.lands.planting-seasons.activities.show', [$land, $plantingSeason, $activity])
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(Land $land, PlantingSeason $plantingSeason, Activity $activity): View
    {
        Gate::authorize('view', $activity);

        return view('farmer.activities.show', ['land' => $land, 'season' => $plantingSeason, 'activity' => $activity]);
    }

    public function edit(Land $land, PlantingSeason $plantingSeason, Activity $activity): View
    {
        Gate::authorize('update', $activity);

        return view('farmer.activities.edit', ['land' => $land, 'season' => $plantingSeason, 'activity' => $activity]);
    }

    public function update(ActivityRequest $request, Land $land, PlantingSeason $plantingSeason, Activity $activity): RedirectResponse
    {
        $this->persistActivity($land, $plantingSeason, $activity, $request->safe()->only(['name', 'scheduled_for', 'status', 'notes']));

        return to_route('farmer.lands.planting-seasons.activities.show', [$land, $plantingSeason, $activity])
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function finish(ActivityActionRequest $request, Land $land, PlantingSeason $plantingSeason, Activity $activity): RedirectResponse
    {
        $changed = $this->persistActivity($land, $plantingSeason, $activity, ['status' => ActivityStatus::Finished]);

        return to_route('farmer.lands.planting-seasons.activities.show', [$land, $plantingSeason, $activity])
            ->with('success', $changed ? 'Kegiatan berhasil ditandai selesai.' : 'Kegiatan sudah berstatus selesai.');
    }

    public function destroy(ActivityActionRequest $request, Land $land, PlantingSeason $plantingSeason, Activity $activity): RedirectResponse
    {
        DB::transaction(function () use ($land, $plantingSeason, $activity): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $currentActivity = $season->activities()->whereKey($activity->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('delete', $currentActivity);
            $currentActivity->delete();
        });

        return to_route('farmer.lands.planting-seasons.activities.index', [$land, $plantingSeason])
            ->with('success', 'Kegiatan berhasil dihapus.');
    }

    private function persistActivity(Land $land, PlantingSeason $plantingSeason, Activity $activity, array $data): bool
    {
        return DB::transaction(function () use ($land, $plantingSeason, $activity, $data): bool {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $currentActivity = $season->activities()->whereKey($activity->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $currentActivity);
            $currentActivity->fill($data);

            if (! $currentActivity->isDirty()) {
                return false;
            }

            $currentActivity->save();

            return true;
        });
    }
}
