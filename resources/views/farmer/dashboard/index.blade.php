@extends('layouts.farmer')
@section('title', 'Dashboard')
@section('content')
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">SELAMAT DATANG DI FARMLOG</p><h1>Selamat datang, {{ auth()->user()->name }}</h1><p>Pantau musim, kegiatan, dan hasil panen pertanian Anda.</p></div></div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4"><div class="dashboard-stat farmer-panel"><span class="farmer-feature-icon"><i class="bi bi-map" aria-hidden="true"></i></span><div><span>Jumlah lahan</span><strong>{{ number_format($landCount, 0, ',', '.') }}</strong></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="dashboard-stat farmer-panel"><span class="farmer-feature-icon"><i class="bi bi-flower1" aria-hidden="true"></i></span><div><span>Musim tanam aktif</span><strong>{{ number_format($activeSeasonCount, 0, ',', '.') }}</strong></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="dashboard-stat farmer-panel"><span class="farmer-feature-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></span><div><span>Kegiatan belum selesai</span><strong>{{ number_format($pendingActivityCount, 0, ',', '.') }}</strong></div></div></div>
</div>
<section class="dashboard-quick-actions farmer-panel mb-5" aria-labelledby="quick-actions-heading">
    <div><h2 id="quick-actions-heading">Aksi cepat</h2><p id="quick-activity-help">{{ $activeSeasonCount > 0 ? 'Pilih musim aktif di bawah untuk langsung mencatat kegiatan atau panen.' : 'Pencatatan kegiatan dan panen memerlukan musim tanam aktif. Buka lahan untuk membuat atau mengaktifkan kembali musim.' }}</p></div>
    <div class="dashboard-action-buttons">
        @if($activeSeasonCount > 0)
            <a class="btn btn-success" href="#active-seasons" aria-describedby="quick-activity-help"><i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Catat kegiatan</a>
            <a class="btn btn-success" href="#active-seasons" aria-describedby="quick-activity-help"><i class="bi bi-basket me-2" aria-hidden="true"></i>Catat panen</a>
        @else
            <button class="btn btn-success" type="button" disabled aria-describedby="quick-activity-help"><i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Catat kegiatan</button>
            <button class="btn btn-success" type="button" disabled aria-describedby="quick-activity-help"><i class="bi bi-basket me-2" aria-hidden="true"></i>Catat panen</button>
        @endif
        <a class="btn btn-outline-success" href="#active-seasons">Musim tanam aktif</a>
        <a class="btn btn-outline-secondary" href="{{ route('farmer.lands.index') }}">Lahan saya</a>
    </div>
</section>
<section id="active-seasons" class="dashboard-seasons" aria-labelledby="active-seasons-heading" tabindex="-1">
    <div class="dashboard-section-heading"><div><p class="farmer-eyebrow">SEDANG BERJALAN</p><h2 id="active-seasons-heading">Musim tanam aktif</h2><p>Pilih musim untuk melihat detail atau mencatat kegiatan pada lahan yang tepat.</p></div></div>
    @if($activeSeasons->isEmpty())
        <div class="farmer-panel">
            @if($activeSeasonCount > 0)
                <x-farmer-empty-state icon="bi-flower1" title="Tidak ada musim di halaman ini" description="Kembali ke halaman pertama untuk melihat musim tanam aktif Anda."><a class="btn btn-outline-success" href="{{ route('farmer.dashboard') }}#active-seasons">Halaman pertama</a></x-farmer-empty-state>
            @elseif($landCount === 0)
                <x-farmer-empty-state title="Mulai dari lahan pertama Anda" description="Tambahkan lahan, lalu buat musim tanam untuk mulai mencatat kegiatan pertanian."><a class="btn btn-success" href="{{ route('farmer.lands.create') }}">Tambah lahan</a></x-farmer-empty-state>
            @else
                <x-farmer-empty-state icon="bi-flower1" title="Belum ada musim tanam aktif" description="Buka lahan untuk membuat musim baru atau mengaktifkan kembali musim yang sudah selesai."><a class="btn btn-success" href="{{ route('farmer.lands.index') }}">Buka lahan saya</a></x-farmer-empty-state>
            @endif
        </div>
    @else
        <div class="row g-4">
            @foreach($activeSeasons as $season)
                <div class="col-md-6 col-xl-4">
                    <article class="land-card dashboard-season-card farmer-panel h-100">
                        <div class="land-card-top"><span class="farmer-feature-icon"><i class="bi bi-flower1" aria-hidden="true"></i></span><x-season-status :status="$season->status" /></div>
                        <h3>{{ $season->crop_name }}</h3>
                        <p class="land-location"><i class="bi bi-map" aria-hidden="true"></i><span>{{ $season->land->name }}</span></p>
                        <dl class="season-dates"><div><dt>Tanggal tanam</dt><dd>{{ $season->planted_at->format('d/m/Y') }}</dd></div><div><dt>Perkiraan panen</dt><dd>{{ $season->expected_harvest_at->format('d/m/Y') }}</dd></div></dl>
                        <div class="land-card-bottom dashboard-season-actions">
                            <a class="btn btn-success" href="{{ route('farmer.lands.planting-seasons.activities.create', [$season->land_id, $season]) }}" aria-label="Catat kegiatan {{ $season->crop_name }} di {{ $season->land->name }} tanggal tanam {{ $season->planted_at->format('d/m/Y') }}"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Catat kegiatan</a>
                            <a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.harvests.create', [$season->land_id, $season]) }}" aria-label="Catat panen {{ $season->crop_name }} di {{ $season->land->name }} tanggal tanam {{ $season->planted_at->format('d/m/Y') }}"><i class="bi bi-basket me-2" aria-hidden="true"></i>Catat panen</a>
                            <a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.show', [$season->land_id, $season]) }}" aria-label="Detail musim {{ $season->crop_name }} di {{ $season->land->name }} tanggal tanam {{ $season->planted_at->format('d/m/Y') }}">Detail musim</a>
                            <a class="btn btn-outline-secondary" href="{{ route('farmer.lands.planting-seasons.activities.index', [$season->land_id, $season]) }}" aria-label="Lihat kegiatan {{ $season->crop_name }} di {{ $season->land->name }} tanggal tanam {{ $season->planted_at->format('d/m/Y') }}">Lihat kegiatan</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        <div class="farmer-pagination mt-4">{{ $activeSeasons->links('pagination.farmer', ['resourceLabel' => 'musim aktif']) }}</div>
    @endif
</section>
<section id="pending-activities" class="dashboard-section mt-5" aria-labelledby="pending-activities-heading" tabindex="-1">
    <div class="dashboard-section-heading"><h2 id="pending-activities-heading">Kegiatan belum selesai</h2><p>Urutan tanggal terlama lebih dahulu. Kegiatan pada musim selesai tetap tersimpan dan hanya dapat dibaca.</p></div>
    <div class="farmer-panel">
        @if($pendingActivities->isEmpty())
            @if($pendingActivityCount > 0)
                <x-farmer-empty-state icon="bi-calendar-check" title="Tidak ada kegiatan di halaman ini" description="Kembali ke halaman pertama untuk melihat kegiatan yang belum selesai."><a class="btn btn-outline-success" href="{{ route('farmer.dashboard', request()->except('activities_page')) }}#pending-activities">Halaman pertama</a></x-farmer-empty-state>
            @else
                <x-farmer-empty-state icon="bi-calendar-check" title="Tidak ada kegiatan tertunda" description="Kegiatan yang direncanakan akan muncul di sini agar mudah Anda pantau." />
            @endif
        @else
            <div class="table-responsive"><table class="table activity-table mb-0"><caption class="visually-hidden">Kegiatan belum selesai milik petani</caption><thead><tr><th scope="col">Kegiatan dan musim</th><th scope="col">Tanggal</th><th scope="col">Status musim</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead><tbody>
                @foreach($pendingActivities as $activity)
                    @php($activitySeason = $activity->plantingSeason)
                    <tr><td class="activity-name">{{ $activity->name }}<span class="dashboard-table-context">{{ $activitySeason->land->name }} · {{ $activitySeason->crop_name }} ({{ $activitySeason->planted_at->format('d/m/Y') }})</span></td><td>{{ $activity->scheduled_for->format('d/m/Y') }}</td><td><x-season-status :status="$activitySeason->status" /></td><td><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.activities.show', [$activitySeason->land_id, $activitySeason, $activity]) }}" aria-label="Detail {{ $activity->name }} tanggal {{ $activity->scheduled_for->format('d/m/Y') }}">Lihat kegiatan</a></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </div>
    <div class="farmer-pagination mt-4">{{ $pendingActivities->links('pagination.farmer', ['resourceLabel' => 'kegiatan']) }}</div>
</section>
<section id="harvest-summary" class="dashboard-section mt-5" aria-labelledby="harvest-summary-heading" tabindex="-1">
    <div class="dashboard-section-heading"><h2 id="harvest-summary-heading">Ringkasan hasil panen</h2><p>Total setiap musim dengan catatan panen, termasuk musim yang sudah selesai. Kilogram dan ton ditampilkan terpisah.</p></div>
    <div class="farmer-panel">
        @if($harvestSeasons->isEmpty())
            @if($harvestSeasons->total() > 0)
                <x-farmer-empty-state icon="bi-basket" title="Tidak ada ringkasan di halaman ini" description="Kembali ke halaman pertama untuk melihat hasil panen."><a class="btn btn-outline-success" href="{{ route('farmer.dashboard', request()->except('harvest_page')) }}#harvest-summary">Halaman pertama</a></x-farmer-empty-state>
            @else
                <x-farmer-empty-state icon="bi-basket" title="Belum ada hasil panen" description="Catat panen pada musim aktif untuk melihat ringkasan hasil di sini." />
            @endif
        @else
            <div class="table-responsive"><table class="table activity-table mb-0"><caption class="visually-hidden">Total panen per musim milik petani</caption><thead><tr><th scope="col">Musim dan lahan</th><th scope="col">Total kg</th><th scope="col">Total ton</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead><tbody>
                @foreach($harvestSeasons as $harvestSeason)
                    <tr><td class="activity-name">{{ $harvestSeason->crop_name }}<span class="dashboard-table-context">{{ $harvestSeason->land->name }} · {{ $harvestSeason->planted_at->format('d/m/Y') }}</span></td><td><x-harvest-quantity :quantity="$harvestSeason->total_kg" unit="kg" /></td><td><x-harvest-quantity :quantity="$harvestSeason->total_ton" unit="ton" /></td><td><a class="btn btn-outline-success" href="{{ route('farmer.lands.planting-seasons.harvests.index', [$harvestSeason->land_id, $harvestSeason]) }}" aria-label="Lihat panen {{ $harvestSeason->crop_name }} di {{ $harvestSeason->land->name }} tanggal tanam {{ $harvestSeason->planted_at->format('d/m/Y') }}">Lihat panen</a></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </div>
    <div class="farmer-pagination mt-4">{{ $harvestSeasons->links('pagination.farmer', ['resourceLabel' => 'musim dengan panen']) }}</div>
</section>
@endsection
