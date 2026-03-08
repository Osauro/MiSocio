@if ($paginator->hasPages())
    <nav aria-label="Paginación">
        <ul class="pagination pagination-sm mb-0 gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link rounded px-2" aria-hidden="true"><i class="fa-solid fa-chevron-left" style="font-size:0.7rem;"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link rounded px-2" href="{{ $paginator->previousPageUrl() }}" rel="prev" wire:navigate><i class="fa-solid fa-chevron-left" style="font-size:0.7rem;"></i></a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link border-0 bg-transparent px-1">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link rounded px-2 fw-bold">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link rounded px-2" href="{{ $url }}" wire:navigate>{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded px-2" href="{{ $paginator->nextPageUrl() }}" rel="next" wire:navigate><i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link rounded px-2" aria-hidden="true"><i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
