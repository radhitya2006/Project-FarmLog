<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\DeleteHarvestRequest;
use App\Http\Requests\Farmer\HarvestRequest;
use App\Models\Harvest;
use App\Models\Land;
use App\Models\PlantingSeason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HarvestController extends Controller
{
    public function index(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('view', $plantingSeason);
        Gate::authorize('viewAny', Harvest::class);
        $harvests = $plantingSeason->harvests()
            ->orderByDesc('harvested_at')->orderByDesc('id')->paginate(12);
        $season = $land->plantingSeasons()->withHarvestTotals()->withCount('harvests')->findOrFail($plantingSeason->id);

        return view('farmer.harvests.index', compact('land', 'season', 'harvests'));
    }

    public function create(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('create', [Harvest::class, $plantingSeason]);

        return view('farmer.harvests.create', ['land' => $land, 'season' => $plantingSeason, 'harvest' => new Harvest]);
    }

    public function store(HarvestRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        $harvest = DB::transaction(function () use ($request, $land, $plantingSeason): Harvest {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('create', [Harvest::class, $season]);
            $this->validateHarvestDate($season, $request->validated('harvested_at'));

            return $season->harvests()->create($request->safe()->only(['harvested_at', 'quantity', 'unit', 'notes']));
        });

        return to_route('farmer.lands.planting-seasons.harvests.show', [$land, $plantingSeason, $harvest])->with('success', 'Hasil panen berhasil dicatat.');
    }

    public function show(Land $land, PlantingSeason $plantingSeason, Harvest $harvest): View
    {
        Gate::authorize('view', $harvest);

        return view('farmer.harvests.show', ['land' => $land, 'season' => $plantingSeason, 'harvest' => $harvest]);
    }

    public function edit(Land $land, PlantingSeason $plantingSeason, Harvest $harvest): View
    {
        Gate::authorize('update', $harvest);

        return view('farmer.harvests.edit', ['land' => $land, 'season' => $plantingSeason, 'harvest' => $harvest]);
    }

    public function update(HarvestRequest $request, Land $land, PlantingSeason $plantingSeason, Harvest $harvest): RedirectResponse
    {
        DB::transaction(function () use ($request, $land, $plantingSeason, $harvest): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $currentHarvest = $season->harvests()->whereKey($harvest->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $currentHarvest);
            $this->validateHarvestDate($season, $request->validated('harvested_at'));
            $currentHarvest->update($request->safe()->only(['harvested_at', 'quantity', 'unit', 'notes']));
        });

        return to_route('farmer.lands.planting-seasons.harvests.show', [$land, $plantingSeason, $harvest])->with('success', 'Catatan panen berhasil diperbarui.');
    }

    public function destroy(DeleteHarvestRequest $request, Land $land, PlantingSeason $plantingSeason, Harvest $harvest): RedirectResponse
    {
        DB::transaction(function () use ($land, $plantingSeason, $harvest): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $currentHarvest = $season->harvests()->whereKey($harvest->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('delete', $currentHarvest);
            $currentHarvest->delete();
        });

        return to_route('farmer.lands.planting-seasons.harvests.index', [$land, $plantingSeason])->with('success', 'Catatan panen berhasil dihapus.');
    }

    private function validateHarvestDate(PlantingSeason $season, string $date): void
    {
        if ($date < $season->planted_at->toDateString()) {
            throw ValidationException::withMessages(['harvested_at' => 'Tanggal panen tidak boleh sebelum tanggal tanam.']);
        }
    }
}
