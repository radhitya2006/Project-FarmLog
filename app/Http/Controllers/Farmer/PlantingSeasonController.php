<?php

namespace App\Http\Controllers\Farmer;

use App\Enums\PlantingSeasonStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\ConfirmPlantingSeasonActionRequest;
use App\Http\Requests\Farmer\PlantingSeasonRequest;
use App\Models\Land;
use App\Models\PlantingSeason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlantingSeasonController extends Controller
{
    public function index(Request $request, Land $land): View
    {
        Gate::authorize('view', $land);
        Gate::authorize('viewAny', PlantingSeason::class);
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(PlantingSeasonStatus::class)],
        ], [
            'status.enum' => 'Pilih status musim tanam yang tersedia.',
        ]);
        $status = $filters['status'] ?? '';
        $seasons = $land->plantingSeasons()
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')->paginate(12)->withQueryString();

        return view('farmer.planting-seasons.index', compact('land', 'seasons', 'status'));
    }

    public function create(Land $land): View
    {
        Gate::authorize('create', [PlantingSeason::class, $land]);

        return view('farmer.planting-seasons.create', ['land' => $land, 'season' => new PlantingSeason]);
    }

    public function store(PlantingSeasonRequest $request, Land $land): RedirectResponse
    {
        $season = $land->plantingSeasons()->create([
            ...$request->safe()->only(['crop_name', 'planted_at', 'expected_harvest_at', 'notes']),
            'status' => PlantingSeasonStatus::Active,
        ]);

        return to_route('farmer.lands.planting-seasons.show', [$land, $season])
            ->with('success', 'Musim tanam berhasil ditambahkan.');
    }

    public function show(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('view', $plantingSeason);
        $plantingSeason->loadCount(['activities', 'harvests']);

        return view('farmer.planting-seasons.show', ['land' => $land, 'season' => $plantingSeason]);
    }

    public function edit(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('update', $plantingSeason);

        return view('farmer.planting-seasons.edit', ['land' => $land, 'season' => $plantingSeason]);
    }

    public function update(PlantingSeasonRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        DB::transaction(function () use ($request, $land, $plantingSeason): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $season);
            $season->update($request->safe()->only(['crop_name', 'planted_at', 'expected_harvest_at', 'notes']));
        });

        return to_route('farmer.lands.planting-seasons.show', [$land, $plantingSeason])
            ->with('success', 'Informasi musim tanam berhasil diperbarui.');
    }

    public function finish(ConfirmPlantingSeasonActionRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        return $this->changeStatus($land, $plantingSeason, PlantingSeasonStatus::Finished);
    }

    public function reopen(ConfirmPlantingSeasonActionRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        return $this->changeStatus($land, $plantingSeason, PlantingSeasonStatus::Active);
    }

    public function destroy(ConfirmPlantingSeasonActionRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        // The database cascades activities and harvests atomically with this delete.
        $plantingSeason->delete();

        return to_route('farmer.lands.planting-seasons.index', $land)
            ->with('success', 'Musim tanam beserta kegiatan dan panen terkait berhasil dihapus.');
    }

    private function changeStatus(Land $land, PlantingSeason $plantingSeason, PlantingSeasonStatus $status): RedirectResponse
    {
        $changed = DB::transaction(function () use ($land, $plantingSeason, $status): bool {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            Gate::authorize($status === PlantingSeasonStatus::Finished ? 'finish' : 'reopen', $season);

            if ($season->status === $status) {
                return false;
            }

            $season->update(['status' => $status]);

            return true;
        });

        $message = $status === PlantingSeasonStatus::Finished
            ? ($changed ? 'Musim tanam diselesaikan. Informasi musim, kegiatan, dan panen kini terkunci.' : 'Musim tanam sudah berstatus selesai.')
            : ($changed ? 'Musim tanam diaktifkan kembali. Informasi musim dapat diedit.' : 'Musim tanam sudah berstatus aktif.');

        return to_route('farmer.lands.planting-seasons.show', [$land, $plantingSeason])->with('success', $message);
    }
}
