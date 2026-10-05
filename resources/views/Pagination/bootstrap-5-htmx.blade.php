@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">« Sebelumnya</span></li>
            @else
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $paginator->previousPageUrl() }}"
                       hx-get="{{ $paginator->previousPageUrl() }}"
                       hx-target="#{{ $paginator->getPageName() === 'masuk_page' ? 'sec-masuk' : 'sec-keluar' }}-content"
                       hx-swap="innerHTML"
                       hx-push-url="true"
                       rel="prev">« Sebelumnya</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link"
                                   href="{{ $url }}"
                                   hx-get="{{ $url }}"
                                   hx-target="#{{ $paginator->getPageName() === 'masuk_page' ? 'sec-masuk' : 'sec-keluar' }}-content"
                                   hx-swap="innerHTML"
                                   hx-push-url="true">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link"
                       href="{{ $paginator->nextPageUrl() }}"
                       hx-get="{{ $paginator->nextPageUrl() }}"
                       hx-target="#{{ $paginator->getPageName() === 'masuk_page' ? 'sec-masuk' : 'sec-keluar' }}-content"
                       hx-swap="innerHTML"
                       hx-push-url="true"
                       rel="next">Berikutnya »</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">Berikutnya »</span></li>
            @endif
        </ul>
    </nav>
@endif