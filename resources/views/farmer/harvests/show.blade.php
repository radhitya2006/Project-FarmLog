@extends('layouts.farmer')
@section('title', 'Detail Panen')
@section('breadcrumb', 'Detail panen')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.harvests.index', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke hasil panen</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">DETAIL HASIL</p><h1>Panen {{ $harvest->harvested_at->format('d/m/Y') }}</h1></div></div>
@include('farmer.harvests._context')
<div class="row g-4"><div class="col-lg-8"><section class="farmer-panel farmer-details"><h2>Informasi panen</h2><dl class="mb-0"><div><dt>Tanggal panen</dt><dd>{{ $harvest->harvested_at->format('d/m/Y') }}</dd></div><div><dt>Hasil panen</dt><dd><x-harvest-quantity :quantity="$harvest->quantity" :unit="$harvest->unit->value" /></dd></div><div><dt>Catatan</dt><dd class="land-notes">{{ $harvest->notes ?? 'Belum ada catatan tambahan.' }}</dd></div></dl></section></div>
    @can('update', $harvest)<div class="col-lg-4"><section class="farmer-panel farmer-details"><h2>Kelola panen</h2><a class="btn btn-success w-100 mt-3" href="{{ route('farmer.lands.planting-seasons.harvests.edit', [$land, $season, $harvest]) }}">Edit catatan panen</a></section><section class="farmer-panel farmer-delete-panel mt-4"><h2>Hapus catatan panen</h2><p>Hanya catatan panen ini yang dihapus. Total panen musim akan dihitung ulang.</p><button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteHarvestModal">Hapus catatan panen</button></section></div>@endcan
</div>
@can('delete', $harvest)<x-confirm-action-modal id="deleteHarvestModal" title="Hapus catatan panen ini?" :action="route('farmer.lands.planting-seasons.harvests.destroy', [$land, $season, $harvest])" label="Ya, hapus panen">Panen tanggal {{ $harvest->harvested_at->format('d/m/Y') }} sebanyak <strong><x-harvest-quantity :quantity="$harvest->quantity" :unit="$harvest->unit->value" /></strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</x-confirm-action-modal>@endcan
@endsection
