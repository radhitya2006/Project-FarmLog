<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\LandRequest;
use App\Models\Land;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LandController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Land::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:150']], [
            'search.string' => 'Pencarian harus berupa teks.',
            'search.max' => 'Pencarian maksimal 150 karakter.',
        ]);
        $search = trim($filters['search'] ?? '');
        $lands = $request->user()->lands()->withCount('plantingSeasons')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%');
            }))
            ->latest('id')->paginate(12)->withQueryString();

        return view('farmer.lands.index', compact('lands', 'search'));
    }

    public function create(): View
    {
        Gate::authorize('create', Land::class);

        return view('farmer.lands.create', ['land' => new Land]);
    }

    public function store(LandRequest $request): RedirectResponse
    {
        $land = $request->user()->lands()->create($request->safe()->only(['name', 'area_hectares', 'location', 'notes']));

        return to_route('farmer.lands.show', $land)->with('success', 'Lahan berhasil ditambahkan.');
    }

    public function show(Land $land): View
    {
        Gate::authorize('view', $land);
        $land->loadCount('plantingSeasons');

        return view('farmer.lands.show', compact('land'));
    }

    public function edit(Land $land): View
    {
        Gate::authorize('update', $land);

        return view('farmer.lands.edit', compact('land'));
    }

    public function update(LandRequest $request, Land $land): RedirectResponse
    {
        $land->update($request->safe()->only(['name', 'area_hectares', 'location', 'notes']));

        return to_route('farmer.lands.show', $land)->with('success', 'Data lahan berhasil diperbarui.');
    }

    public function destroy(Land $land): RedirectResponse
    {
        Gate::authorize('delete', $land);

        if ($land->plantingSeasons()->exists()) {
            return $this->cannotDelete($land);
        }

        try {
            $land->delete();
        } catch (QueryException $exception) {
            // A season may be created between the check and deletion. MySQL RESTRICT remains authoritative.
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1451) {
                throw $exception;
            }

            return $this->cannotDelete($land);
        }

        return to_route('farmer.lands.index')->with('success', 'Lahan berhasil dihapus.');
    }

    private function cannotDelete(Land $land): RedirectResponse
    {
        return to_route('farmer.lands.show', $land)->with('error',
            'Lahan tidak dapat dihapus karena masih memiliki musim tanam. Hapus seluruh musim tanam terkait terlebih dahulu.');
    }
}
