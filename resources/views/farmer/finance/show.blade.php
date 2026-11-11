@extends('layouts.farmer')
@section('title', 'Keuangan Musim')
@section('breadcrumb', 'Keuangan musim')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.finance.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Semua keuangan musim</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">KEUANGAN MUSIM</p><h1>{{ $season->crop_name }}</h1><p class="text-break">{{ $land->name }} · Tanam {{ $season->planted_at->format('d/m/Y') }}</p></div><x-season-status :status="$season->status" /></div>
@if($season->status === \App\Enums\PlantingSeasonStatus::Finished)
    <div class="alert alert-secondary" role="status">Musim sudah selesai. Pengeluaran dan penjualan tetap dapat dicatat atau diperbaiki.</div>
@endif
@include('farmer.finance._summary')
<div class="farmer-panel finance-context mb-4">
    <div><strong>Dasar perhitungan biaya/kg</strong><p class="mb-0">Berat panen setara <x-harvest-quantity :quantity="$summary['weight_kg']" unit="kg" />. Untuk perhitungan ini, 1 ton = 1.000 kg.</p></div>
    <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.harvests.index', [$land, $season]) }}">Lihat hasil panen</a><a class="btn btn-outline-secondary" href="{{ route('farmer.lands.planting-seasons.show', [$land, $season]) }}">Detail musim</a></div>
</div>
@include('farmer.finance._transactions', ['records' => $expenses, 'kind' => 'expenses', 'dateField' => 'spent_on', 'heading' => 'Pengeluaran', 'label' => 'pengeluaran'])
@include('farmer.finance._transactions', ['records' => $sales, 'kind' => 'sales', 'dateField' => 'received_on', 'heading' => 'Penjualan', 'label' => 'penjualan'])
@endsection

