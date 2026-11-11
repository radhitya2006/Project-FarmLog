@props(['amount'])
@php
    $value = (string) ($amount ?? '0');
    $negative = str_starts_with($value, '-');
    [$whole, $fraction] = array_pad(explode('.', ltrim($value, '-'), 2), 2, '');
    $formatted = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole).','.str_pad($fraction, 2, '0');
@endphp
<span>{{ $negative ? '−' : '' }}Rp{{ $formatted }}</span>

