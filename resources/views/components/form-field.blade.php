@props(['name', 'label', 'type' => 'text', 'value' => '', 'autocomplete' => null, 'help' => null])
<div class="form-field">
    <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }} <span class="text-danger" aria-hidden="true">*</span></label>
    <div class="{{ $type === 'password' ? 'password-field' : '' }}">
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            class="form-control @error($name) is-invalid @enderror"
            @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @error($name) aria-invalid="true" @enderror
            @if ($errors->has($name) || $help)
                aria-describedby="{{ $errors->has($name) ? $name.'-error' : '' }} {{ $help ? $name.'-help' : '' }}"
            @endif
            required {{ $attributes }}>
        @if ($type === 'password')
            <button class="password-toggle" type="button" data-password-toggle="{{ $name }}"
                data-password-label="{{ strtolower($label) }}" aria-controls="{{ $name }}"
                aria-pressed="false" aria-label="Tampilkan {{ strtolower($label) }}">
                <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
        @endif
    </div>
    @if ($help)
        <div id="{{ $name }}-help" class="form-text">{{ $help }}</div>
    @endif
    @error($name)
        <div id="{{ $name }}-error" class="invalid-feedback d-block" role="alert">{{ $message }}</div>
    @enderror
</div>
