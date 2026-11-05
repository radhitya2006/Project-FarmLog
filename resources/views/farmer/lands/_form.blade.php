@csrf
<div class="row g-4">
    <div class="col-md-8"><x-form-field name="name" label="Nama lahan" :value="$land->name" maxlength="150" placeholder="Contoh: Sawah Subak Utara" /></div>
    <div class="col-md-4"><x-form-field name="area_hectares" label="Luas (hektare)" type="number" :value="$land->area_hectares" min="0.01" max="99999999.99" step="0.01" inputmode="decimal" placeholder="0,00" help="Maksimal 2 angka desimal." /></div>
    <div class="col-12"><x-form-field name="location" label="Lokasi lahan" :value="$land->location" maxlength="255" placeholder="Contoh: Desa Mengwi, Badung, Bali" help="Tuliskan alamat atau penanda lokasi yang mudah dikenali." /></div>
    <div class="col-12">
        <label for="notes" class="form-label fw-semibold">Catatan <span class="text-secondary fw-normal">(opsional)</span></label>
        <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="5" maxlength="5000" aria-describedby="notes-help @error('notes') notes-error @enderror" @error('notes') aria-invalid="true" @enderror placeholder="Contoh: Akses air dari saluran irigasi sebelah timur.">{{ old('notes', $land->notes) }}</textarea>
        <div class="form-text" id="notes-help">Tambahkan informasi penting tentang lahan. Maksimal 5.000 karakter.</div>
        @error('notes')<div id="notes-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
    </div>
</div>
<div class="farmer-form-actions"><a class="btn btn-outline-secondary" href="{{ $land->exists ? route('farmer.lands.show', $land) : route('farmer.lands.index') }}">Batal</a><button class="btn btn-success" type="submit" data-land-submit>{{ $land->exists ? 'Simpan perubahan' : 'Simpan lahan' }}</button></div>
