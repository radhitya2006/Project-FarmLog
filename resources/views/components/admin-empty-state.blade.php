@props(['title', 'description'])
<div class="admin-empty-state"><span class="admin-empty-icon"><i class="bi bi-people" aria-hidden="true"></i></span><h3>{{ $title }}</h3><p>{{ $description }}</p>{{ $slot }}</div>
