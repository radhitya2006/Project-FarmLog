@extends('layouts.farmer')
@section('title', 'Catat Panen')
@section('breadcrumb', 'Catat panen')
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.lands.planting-seasons.harvests.index', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke hasil panen</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">PANEN BARU</p><h1>Catat panen</h1><p>Satu musim dapat memiliki beberapa catatan panen.</p></div></div>
@include('farmer.harvests._context')
<form class="farmer-panel farmer-form" method="POST" action="{{ route('farmer.lands.planting-seasons.harvests.store', [$land, $season]) }}" data-domain-form>@include('farmer.harvests._form')</form>
@endsection
