@extends('layouts.farmer')
@section('title', 'Hasil Panen')
@section('breadcrumb', 'Hasil panen')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.show', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke detail musim</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">CATATAN HASIL</p><h1>Hasil panen</h1><p>Simpan setiap panen dan pantau hasil musim Anda.</p></div>
    @can('create', [\App\Models\Harvest::class, $season])<a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.harvests.create', [$land, $season]) }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Catat panen</a>@endcan
</div>
@include('farmer.harvests._context')
<section class="farmer-panel farmer-details mb-4"><h2>Total panen musim ini</h2><p class="text-secondary small">{{ $season->harvests_count }} catatan panen. Total seluruh musim, tidak dipengaruhi halaman daftar.</p><x-harvest-summary :season="$season" /></section>
<p class="farmer-result-count">{{ $harvests->total() }} catatan panen tercatat</p>
<section class="farmer-panel">
    @if($harvests->isEmpty())
        @if($harvests->total() > 0)
            <x-farmer-empty-state icon="bi-basket" title="Catatan panen tidak ditemukan" description="Lihat semua catatan panen pada musim ini."><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.harvests.index', [$land, $season]) }}">Lihat semua panen</a></x-farmer-empty-state>
        @else
            <x-farmer-empty-state icon="bi-basket" title="Belum ada catatan panen" description="Catat tanggal, jumlah, dan satuan untuk menyimpan hasil panen Anda.">
                @can('create', [\App\Models\Harvest::class, $season])<a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.harvests.create', [$land, $season]) }}">Catat panen pertama</a>@endcan
            </x-farmer-empty-state>
        @endif
    @else
        <div class="table-responsive"><table class="table activity-table mb-0"><caption class="visually-hidden">Panen pada musim {{ $season->crop_name }} di {{ $land->name }}</caption>
            <thead><tr><th scope="col">Tanggal panen</th><th scope="col">Hasil panen</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>@foreach($harvests as $harvest)<tr><td>{{ $harvest->harvested_at->format('d/m/Y') }}</td><td><x-harvest-quantity :quantity="$harvest->quantity" :unit="$harvest->unit->value" /></td><td><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.harvests.show', [$land, $season, $harvest]) }}" aria-label="Detail panen tanggal {{ $harvest->harvested_at->format('d/m/Y') }}, catatan {{ $harvest->id }}">Lihat detail</a></td></tr>@endforeach</tbody>
        </table></div>
    @endif
</section>
<div class="farmer-pagination mt-4">{{ $harvests->links('pagination.farmer', ['resourceLabel' => 'catatan panen']) }}</div>
@endsection
