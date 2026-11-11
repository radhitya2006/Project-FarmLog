<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\DeleteFinancialRecordRequest;
use App\Http\Requests\Farmer\SaleRequest;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function create(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('create', [Sale::class, $plantingSeason]);

        return view('farmer.finance.form', [
            'land' => $land, 'season' => $plantingSeason, 'record' => new Sale,
            'kind' => 'sales', 'dateField' => 'received_on', 'label' => 'penjualan',
        ]);
    }

    public function store(SaleRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        DB::transaction(function () use ($request, $land, $plantingSeason): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('create', [Sale::class, $season]);
            $season->sales()->create($request->safe()->only(['received_on', 'name', 'amount', 'notes']));
        });

        return to_route('farmer.finance.show', [$land, $plantingSeason])->with('success', 'Penjualan berhasil dicatat.');
    }

    public function edit(Land $land, PlantingSeason $plantingSeason, Sale $sale): View
    {
        Gate::authorize('update', $sale);

        return view('farmer.finance.form', [
            'land' => $land, 'season' => $plantingSeason, 'record' => $sale,
            'kind' => 'sales', 'dateField' => 'received_on', 'label' => 'penjualan',
        ]);
    }

    public function update(SaleRequest $request, Land $land, PlantingSeason $plantingSeason, Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($request, $land, $plantingSeason, $sale): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $record = $season->sales()->whereKey($sale->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $record);
            $record->update($request->safe()->only(['received_on', 'name', 'amount', 'notes']));
        });

        return to_route('farmer.finance.show', [$land, $plantingSeason])->with('success', 'Penjualan berhasil diperbarui.');
    }

    public function destroy(DeleteFinancialRecordRequest $request, Land $land, PlantingSeason $plantingSeason, Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($land, $plantingSeason, $sale): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $record = $season->sales()->whereKey($sale->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('delete', $record);
            $record->delete();
        });

        return to_route('farmer.finance.show', [$land, $plantingSeason])->with('success', 'Penjualan berhasil dihapus.');
    }
}
