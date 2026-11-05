@props(['title', 'description', 'icon' => 'bi-map'])
<div class="farmer-empty-state">
    <span class="farmer-feature-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    {{ $slot }}
</div>
