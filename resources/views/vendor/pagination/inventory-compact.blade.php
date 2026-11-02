@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max($current - 1, 1);
        $end = min($current + 1, $last);
        if ($end - $start < 2) {
            if ($start === 1) {
                $end = min($start + 2, $last);
            } else {
                $start = max($end - 2, 1);
            }
        }
    @endphp
    <nav aria-label="Inventory pages" class="inventory-compact-pagination">
        <ul class="pagination mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if($paginator->onFirstPage())
                    <span class="page-link" aria-disabled="true" aria-label="Previous page">&lsaquo;</span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&lsaquo;</a>
                @endif
            </li>
            @for($page = $start; $page <= $end; $page++)
                <li class="page-item d-none d-sm-block {{ $page == $current ? 'active' : '' }}">
                    @if($page == $current)
                        <span class="page-link" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                </li>
            @endfor
            <li class="page-item d-sm-none"><span class="page-link">{{ $current }} / {{ $last }}</span></li>
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&rsaquo;</a>
                @else
                    <span class="page-link" aria-disabled="true" aria-label="Next page">&rsaquo;</span>
                @endif
            </li>
        </ul>
    </nav>
@endif
