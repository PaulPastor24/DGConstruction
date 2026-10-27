@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect([1, $current - 1, $current, $current + 1, $last])
            ->filter(fn ($page) => $page >= 1 && $page <= $last)->unique()->sort()->values();
        $previousNumber = 0;
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
            @foreach($pages as $page)
                @if($previousNumber && $page > $previousNumber + 1)
                    <li class="page-item disabled d-none d-sm-block"><span class="page-link">&hellip;</span></li>
                @endif
                <li class="page-item d-none d-sm-block {{ $page == $current ? 'active' : '' }}">
                    @if($page == $current)
                        <span class="page-link" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                </li>
                @php($previousNumber = $page)
            @endforeach
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
