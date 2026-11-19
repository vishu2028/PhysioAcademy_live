@if ($paginator->hasPages())
    @php
        $paginator->onEachSide(1);
        $window = \Illuminate\Pagination\UrlWindow::make($paginator);
        $ranges = array_values(array_filter([$window['first'], $window['slider'], $window['last']]));
    @endphp
    <nav class="site-search-pagination-nav" aria-label="{{ __('Pagination Navigation') }}">
        @if ($paginator->onFirstPage())
            <span class="site-search-page-control is-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="m14.5 5-7 7 7 7" />
                </svg>
            </span>
        @else
            <a class="site-search-page-control" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="m14.5 5-7 7 7 7" />
                </svg>
            </a>
        @endif

        <div class="site-search-page-numbers">
            @foreach ($ranges as $index => $range)
                @if ($index > 0 && array_key_first($range) > array_key_last($ranges[$index - 1]) + 1)
                    <span class="site-search-page-ellipsis" aria-hidden="true">…</span>
                @endif
                @foreach ($range as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="site-search-page-number is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="site-search-page-number" href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="site-search-page-control" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="m9.5 5 7 7-7 7" />
                </svg>
            </a>
        @else
            <span class="site-search-page-control is-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="m9.5 5 7 7-7 7" />
                </svg>
            </span>
        @endif
    </nav>
@endif
