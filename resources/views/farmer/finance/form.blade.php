@extends('layouts.farmer')
@section('title', ($record->exists ? 'Edit ' : 'Catat ').$label)
@section('breadcrumb', ($record->exists ? 'Edit ' : 'Catat ').$label)
@section('content')
<a class="farmer-back-link" href="{{ route('farmer.finance.show', [$land, $season]) }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke keuangan musim</a>
<div class="farmer-page-heading"><div><p class="farmer-eyebrow">TRANSAKSI MUSIM</p><h1>{{ $record->exists ? 'Edit' : 'Catat' }} {{ $label }}</h1><p>{{ $kind === 'expenses' ? 'Catat pengeluaran yang sudah dibayar.' : 'Catat hasil penjualan yang uangnya sudah diterima.' }}</p></div></div>
<p class="activity-context"><strong>{{ $season->crop_name }}</strong> · {{ $land->name }} · Tanam {{ $season->planted_at->format('d/m/Y') }} <x-season-status :status="$season->status" /></p>
<form class="farmer-panel farmer-form" method="POST" action="{{ $record->exists ? route('farmer.lands.planting-seasons.'.$kind.'.update', [$land, $season, $record]) : route('farmer.lands.planting-seasons.'.$kind.'.store', [$land, $season]) }}" data-domain-form>
    @csrf
    @if($record->exists) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-md-6"><x-form-field :name="$dateField" :label="$kind === 'expenses' ? 'Tanggal pengeluaran' : 'Tanggal penerimaan uang'" type="date" :value="$record->{$dateField}?->format('Y-m-d') ?? now()->toDateString()" help="Tanggal boleh sebelum tanggal tanam." /></div>
        <div class="col-md-6"><x-form-field name="amount" label="Nominal (Rp)" type="number" :value="$record->amount" min="0.01" max="9999999999999.99" step="0.01" inputmode="decimal" help="Nominal positif, maksimal dua angka desimal." /></div>
        <div class="{{ $kind === 'expenses' ? 'col-md-7' : 'col-12' }}"><x-form-field name="name" :label="$kind === 'expenses' ? 'Nama pengeluaran' : 'Keterangan penjualan'" :value="$record->name" maxlength="150" :placeholder="$kind === 'expenses' ? 'Contoh: Pembelian bibit cabai' : 'Contoh: Penjualan panen pertama'" /></div>
        @if($kind === 'expenses')
            <div class="col-md-5"><label for="category" class="form-label fw-semibold">Kategori <span class="text-danger" aria-hidden="true">*</span></label>
                <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required @error('category') aria-invalid="true" aria-describedby="category-error" @enderror>
                    <option value="">Pilih kategori</option>
                    @foreach(\App\Enums\ExpenseCategory::cases() as $category)<option value="{{ $category->value }}" @selected(old('category', $record->category?->value) === $category->value)>{{ $category->label() }}</option>@endforeach
                </select>
                @error('category')<div id="category-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
            </div>
        @endif
        <div class="col-12"><label for="notes" class="form-label fw-semibold">Catatan <span class="text-secondary fw-normal">(opsional)</span></label>
            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4" maxlength="5000" aria-describedby="notes-help @error('notes') notes-error @enderror" @error('notes') aria-invalid="true" @enderror>{{ old('notes', $record->notes) }}</textarea><div class="form-text" id="notes-help">Maksimal 5.000 karakter.</div>
            @error('notes')<div id="notes-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="farmer-form-actions"><a class="btn btn-outline-secondary" href="{{ route('farmer.finance.show', [$land, $season]) }}">Batal</a><button class="btn btn-success" type="submit" data-domain-submit>{{ $record->exists ? 'Simpan perubahan' : 'Simpan '.$label }}</button></div>
</form>
@endsection

