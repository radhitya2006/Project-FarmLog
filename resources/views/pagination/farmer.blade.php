@if ($paginator->hasPages())
    <nav class="d-flex flex-wrap align-items-center justify-content-between gap-3" aria-label="Navigasi halaman {{ $resourceLabel ?? 'lahan' }}">
        <p class="text-secondary mb-0">Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} {{ $resourceLabel ?? 'lahan' }}</p>
        <ul class="pagination flex-wrap mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a></li>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled d-none d-sm-block"><span class="page-link">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <li class="page-item active d-none d-sm-block" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item d-none d-sm-block"><a class="page-link" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">Berikutnya</span></li>
            @endif
        </ul>
    </nav>
@endif
