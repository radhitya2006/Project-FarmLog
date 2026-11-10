@props(['quantity', 'unit'])
@php
    [$whole, $fraction] = array_pad(explode('.', (string) ($quantity ?? '0'), 2), 2, '');
    $formatted = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole).','.str_pad($fraction, 2, '0');
@endphp
<span>{{ $formatted }} {{ $unit }}</span>
