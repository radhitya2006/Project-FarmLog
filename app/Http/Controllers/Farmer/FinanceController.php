<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Land;
use App\Models\PlantingSeason;
use App\Models\Sale;
use App\Support\SeasonFinance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', PlantingSeason::class);
        $ownerId = $request->user()->id;
        $ownedLand = fn ($query) => $query->where('user_id', $ownerId);
        $seasons = PlantingSeason::query()->whereHas('land', $ownedLand)->with('land:id,name')
            ->withFinancialTotals()->orderByDesc('planted_at')->orderByDesc('id')->paginate(12);
        $expenses = (string) Expense::query()->whereHas('plantingSeason.land', $ownedLand)->sum('amount');
        $sales = (string) Sale::query()->whereHas('plantingSeason.land', $ownedLand)->sum('amount');
        $summary = ['expenses' => $expenses, 'sales' => $sales, 'balance' => bcsub($sales, $expenses, 2)];

        return view('farmer.finance.index', compact('seasons', 'summary'));
    }

    public function show(Land $land, PlantingSeason $plantingSeason): View
    {
        Gate::authorize('view', $plantingSeason);
        $season = $land->plantingSeasons()->withFinancialTotals()->withHarvestTotals()->findOrFail($plantingSeason->id);
        $summary = SeasonFinance::summarize($season);
        $expenses = $season->expenses()->orderByDesc('spent_on')->orderByDesc('id')
            ->paginate(10, ['*'], 'expenses_page')->withQueryString()->fragment('expenses');
        $sales = $season->sales()->orderByDesc('received_on')->orderByDesc('id')
            ->paginate(10, ['*'], 'sales_page')->withQueryString()->fragment('sales');

        return view('farmer.finance.show', compact('land', 'season', 'summary', 'expenses', 'sales'));
    }
}
