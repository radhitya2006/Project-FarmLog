@props(['name', 'label', 'type' => 'text', 'value' => '', 'autocomplete' => null])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }} <span class="text-danger" aria-hidden="true">*</span></label>
    <div class="{{ $type === 'password' ? 'input-group' : '' }}">
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            class="form-control @error($name) is-invalid @enderror"
            @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
            required {{ $attributes }}>
        @if ($type === 'password')
            <button class="btn btn-outline-secondary" type="button" data-password-toggle="{{ $name }}" aria-controls="{{ $name }}" aria-pressed="false" aria-label="Tampilkan {{ strtolower($label) }}">Lihat</button>
        @endif
        @error($name)
            <div id="{{ $name }}-error" class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
