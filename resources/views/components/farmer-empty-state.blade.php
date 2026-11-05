@props(['title', 'description'])
<div class="farmer-empty-state">
    <span class="farmer-feature-icon"><i class="bi bi-map" aria-hidden="true"></i></span>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    {{ $slot }}
</div>
