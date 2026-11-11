@extends('layouts.farmer')
@section('title', 'Keuangan')
@section('breadcrumb', 'Keuangan')
@section('content')
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">CATATAN USAHA TANI</p><h1>Keuangan</h1><p>Pantau pengeluaran dan penjualan dari seluruh musim tanam Anda.</p></div></div>
@include('farmer.finance._summary')
<section aria-labelledby="finance-seasons-heading">
    <div class="dashboard-section-heading"><h2 id="finance-seasons-heading">Pilih musim tanam</h2><p>Buka musim untuk mencatat transaksi dan melihat biaya per kilogram panennya.</p></div>
    @if($seasons->isEmpty())
        <div class="farmer-panel">
            @if($seasons->total() > 0)
                <x-farmer-empty-state title="Tidak ada musim di halaman ini" description="Kembali ke halaman pertama untuk memilih musim."><a class="btn btn-outline-success" href="{{ route('farmer.finance.index') }}">Halaman pertama</a></x-farmer-empty-state>
            @else
                <x-farmer-empty-state title="Belum ada musim tanam" description="Buat lahan dan musim tanam terlebih dahulu untuk mulai mencatat keuangan."><a class="btn btn-success" href="{{ route('farmer.lands.index') }}">Buka lahan saya</a></x-farmer-empty-state>
            @endif
        </div>
    @else
        <div class="row g-4">
            @foreach($seasons as $season)
                <div class="col-md-6 col-xl-4"><article class="farmer-panel land-card h-100">
                    <div class="land-card-top"><span class="farmer-feature-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span><x-season-status :status="$season->status" /></div>
                    <h2>{{ $season->crop_name }}</h2><p class="text-break mb-2">{{ $season->land->name }}</p>
                    <p class="finance-explanation">Tanam {{ $season->planted_at->format('d/m/Y') }} · Perkiraan panen {{ $season->expected_harvest_at->format('d/m/Y') }}</p>
                    <dl class="finance-season-totals">
                        <div><dt>Pengeluaran</dt><dd><x-money :amount="$season->total_expenses" /></dd></div>
                        <div><dt>Penjualan</dt><dd><x-money :amount="$season->total_sales" /></dd></div>
                        <div><dt>Selisih</dt><dd><x-money :amount="bcsub((string) ($season->total_sales ?? '0'), (string) ($season->total_expenses ?? '0'), 2)" /></dd></div>
                    </dl>
                    <a class="btn btn-outline-success w-100" href="{{ route('farmer.finance.show', [$season->land, $season]) }}" aria-label="Buka keuangan {{ $season->crop_name }} di {{ $season->land->name }}, tanam {{ $season->planted_at->format('d/m/Y') }}">Buka keuangan musim <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
                </article></div>
            @endforeach
        </div>
        <div class="farmer-pagination mt-4">{{ $seasons->links('pagination.farmer') }}</div>
    @endif
</section>
@endsection

