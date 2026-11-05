@csrf
<div class="row g-4">
    <div class="col-12"><x-form-field name="crop_name" label="Komoditas" :value="$season->crop_name" maxlength="150" placeholder="Contoh: Padi, cabai, atau tomat" /></div>
    <div class="col-md-6"><x-form-field name="planted_at" label="Tanggal tanam" type="date" :value="$season->planted_at?->format('Y-m-d')" /></div>
    <div class="col-md-6"><x-form-field name="expected_harvest_at" label="Perkiraan tanggal panen" type="date" :value="$season->expected_harvest_at?->format('Y-m-d')" help="Tidak boleh sebelum tanggal tanam." /></div>
    <div class="col-12">
        <label for="notes" class="form-label fw-semibold">Catatan <span class="text-secondary fw-normal">(opsional)</span></label>
        <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="5" maxlength="5000" aria-describedby="notes-help @error('notes') notes-error @enderror" @error('notes') aria-invalid="true" @enderror placeholder="Tambahkan informasi penting tentang musim tanam ini.">{{ old('notes', $season->notes) }}</textarea>
        <div class="form-text" id="notes-help">Maksimal 5.000 karakter.</div>
        @error('notes')<div id="notes-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
    </div>
</div>
<div class="farmer-form-actions">
    <a class="btn btn-outline-secondary" href="{{ $season->exists ? route('farmer.lands.planting-seasons.show', [$land, $season]) : route('farmer.lands.planting-seasons.index', $land) }}">Batal</a>
    <button class="btn btn-success" type="submit" data-season-submit>{{ $season->exists ? 'Simpan perubahan' : 'Simpan musim tanam' }}</button>
</div>
