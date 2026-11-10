@csrf
<div class="row g-4">
    <div class="col-12"><x-form-field name="harvested_at" label="Tanggal panen" type="date" :value="$harvest->harvested_at?->format('Y-m-d')" :min="$season->planted_at->format('Y-m-d')" help="Tidak boleh sebelum tanggal tanam." /></div>
    <div class="col-md-7"><x-form-field name="quantity" label="Jumlah hasil panen" type="number" :value="$harvest->quantity" min="0.01" max="9999999999.99" step="0.01" inputmode="decimal" help="Jumlah positif, maksimal dua angka desimal." /></div>
    <div class="col-md-5"><label for="unit" class="form-label fw-semibold">Satuan <span class="text-danger" aria-hidden="true">*</span></label>
        <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror" required @error('unit') aria-invalid="true" aria-describedby="unit-error" @enderror><option value="kg" @selected(old('unit', $harvest->unit?->value ?? 'kg') === 'kg')>Kilogram (kg)</option><option value="ton" @selected(old('unit', $harvest->unit?->value) === 'ton')>Ton</option></select>
        @error('unit')<div id="unit-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
    </div>
    <div class="col-12"><label for="notes" class="form-label fw-semibold">Catatan <span class="text-secondary fw-normal">(opsional)</span></label>
        <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="5" maxlength="5000" aria-describedby="notes-help @error('notes') notes-error @enderror" @error('notes') aria-invalid="true" @enderror placeholder="Contoh: Panen pertama, kondisi hasil, atau keterangan lainnya.">{{ old('notes', $harvest->notes) }}</textarea><div id="notes-help" class="form-text">Maksimal 5.000 karakter.</div>
        @error('notes')<div id="notes-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
    </div>
</div>
<div class="farmer-form-actions"><a class="btn btn-outline-secondary" href="{{ $harvest->exists ? route('farmer.lands.planting-seasons.harvests.show', [$land, $season, $harvest]) : route('farmer.lands.planting-seasons.harvests.index', [$land, $season]) }}">Batal</a><button class="btn btn-success" type="submit" data-domain-submit>{{ $harvest->exists ? 'Simpan perubahan' : 'Simpan panen' }}</button></div>
