@csrf
<div class="row g-4">
    <div class="col-12"><x-form-field name="name" label="Nama kegiatan" :value="$activity->name" maxlength="150" placeholder="Contoh: Pemupukan pertama" /></div>
    <div class="col-md-6"><x-form-field name="scheduled_for" label="Tanggal kegiatan" type="date" :value="$activity->scheduled_for?->format('Y-m-d')" help="Boleh sebelum tanggal tanam untuk persiapan lahan." /></div>
    <div class="col-md-6">
        <label for="status" class="form-label fw-semibold">Status kegiatan <span class="text-danger" aria-hidden="true">*</span></label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
            <option value="direncanakan" @selected(old('status', $activity->status?->value ?? 'direncanakan') === 'direncanakan')>Direncanakan</option>
            <option value="selesai" @selected(old('status', $activity->status?->value) === 'selesai')>Selesai</option>
        </select>
        @error('status')<div id="status-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="notes" class="form-label fw-semibold">Keterangan <span class="text-secondary fw-normal">(opsional)</span></label>
        <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="5" maxlength="5000" aria-describedby="notes-help @error('notes') notes-error @enderror" @error('notes') aria-invalid="true" @enderror placeholder="Catat bahan, cara pelaksanaan, atau hal penting lainnya.">{{ old('notes', $activity->notes) }}</textarea>
        <div class="form-text" id="notes-help">Maksimal 5.000 karakter.</div>
        @error('notes')<div id="notes-error" class="invalid-feedback" role="alert">{{ $message }}</div>@enderror
    </div>
</div>
<div class="farmer-form-actions"><a class="btn btn-outline-secondary" href="{{ $activity->exists ? route('farmer.lands.planting-seasons.activities.show', [$land, $season, $activity]) : route('farmer.lands.planting-seasons.activities.index', [$land, $season]) }}">Batal</a><button class="btn btn-success" type="submit" data-domain-submit>{{ $activity->exists ? 'Simpan perubahan' : 'Simpan kegiatan' }}</button></div>
