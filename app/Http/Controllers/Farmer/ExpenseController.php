<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\DeleteFinancialRecordRequest;
use App\Http\Requests\Farmer\ExpenseRequest;
use App\Models\Expense;
use App\Models\Land;
use App\Models\PlantingSeason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function create(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('create', [Expense::class, $plantingSeason]);

        return view('farmer.finance.form', [
            'land' => $land, 'season' => $plantingSeason, 'record' => new Expense,
            'kind' => 'expenses', 'dateField' => 'spent_on', 'label' => 'pengeluaran',
        ]);
    }

    public function store(ExpenseRequest $request, Land $land, PlantingSeason $plantingSeason): RedirectResponse
    {
        DB::transaction(function () use ($request, $land, $plantingSeason): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('create', [Expense::class, $season]);
            $season->expenses()->create($request->safe()->only(['spent_on', 'name', 'category', 'amount', 'notes']));
        });

        return to_route('farmer.finance.show', [$land, $plantingSeason])->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Land $land, PlantingSeason $plantingSeason, Expense $expense): View
    {
        Gate::authorize('update', $expense);

        return view('farmer.finance.form', [
            'land' => $land, 'season' => $plantingSeason, 'record' => $expense,
            'kind' => 'expenses', 'dateField' => 'spent_on', 'label' => 'pengeluaran',
        ]);
    }

    public function update(ExpenseRequest $request, Land $land, PlantingSeason $plantingSeason, Expense $expense): RedirectResponse
    {
        DB::transaction(function () use ($request, $land, $plantingSeason, $expense): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $record = $season->expenses()->whereKey($expense->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $record);
            $record->update($request->safe()->only(['spent_on', 'name', 'category', 'amount', 'notes']));
        });

        return to_route('farmer.finance.show', [$land, $plantingSeason])->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(DeleteFinancialRecordRequest $request, Land $land, PlantingSeason $plantingSeason, Expense $expense): RedirectResponse
    {
        DB::transaction(function () use ($land, $plantingSeason, $expense): void {
            $season = $land->plantingSeasons()->whereKey($plantingSeason->id)->lockForUpdate()->firstOrFail();
            $record = $season->expenses()->whereKey($expense->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('delete', $record);
            $record->delete();
        });

        return to_route('farmer.finance.show', [$land, $plantingSeason])->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
