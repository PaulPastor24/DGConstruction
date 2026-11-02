@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <ul class="pagination">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link" aria-disabled="true" aria-label="Previous page">&lsaquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&lsaquo;</a>
                </li>
            @endif

            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $window = 1; // show current-1, current, current+1 => 3 pages
                $start = max($current - $window, 1);
                $end = min($current + $window, $last);
                // pad so we always show 3 pages when available
                if ($end - $start < 2) {
                    if ($start === 1) {
                        $end = min($start + 2, $last);
                    } else {
                        $start = max($end - 2, 1);
                    }
                }
            @endphp

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $current)
                    <li class="page-item active">
                        <span class="page-link" aria-current="page">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&rsaquo;</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link" aria-disabled="true" aria-label="Next page">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
