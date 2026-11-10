@extends('layouts.farmer')
@section('title', 'Edit Panen')
@section('breadcrumb', 'Edit panen')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.harvests.show', [$land, $season, $harvest]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke detail panen</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">INFORMASI PANEN</p><h1>Edit catatan panen</h1><p>Perbarui tanggal, jumlah, dan satuan hasil panen Anda.</p></div></div>
@include('farmer.harvests._context')
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.planting-seasons.harvests.update', [$land, $season, $harvest]) }}" data-domain-form>@method('PUT') @include('farmer.harvests._form')</form>
@endsection
