@props(['label'])
<button type="submit" class="btn btn-success auth-submit" data-original-label="{{ $label }}">
    <span class="spinner-border spinner-border-sm d-none" data-submit-spinner aria-hidden="true"></span>
    <span data-submit-label>{{ $label }}</span>
    <i class="bi bi-arrow-right" data-submit-icon aria-hidden="true"></i>
</button>
