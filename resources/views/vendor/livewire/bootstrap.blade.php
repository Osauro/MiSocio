@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

@if ($paginator->hasPages())
@php
    $lastPage    = $paginator->lastPage();
    $currentPage = $paginator->currentPage();

    $pages = collect();
    // Primeras 2
    $pages->push(1);
    if ($lastPage > 1) $pages->push(2);
    // Alrededor de la página actual (±1)
    for ($i = max(1, $currentPage - 1); $i <= min($lastPage, $currentPage + 1); $i++) {
        $pages->push($i);
    }
    // Últimas 3
    for ($i = max(1, $lastPage - 2); $i <= $lastPage; $i++) {
        $pages->push($i);
    }
    $pages = $pages->unique()->sort()->values();

    // Construir lista con separadores
    $display = collect();
    $prev = null;
    foreach ($pages as $page) {
        if ($prev !== null && $page > $prev + 1) {
            $display->push('...');
        }
        $display->push($page);
        $prev = $page;
    }
@endphp

<nav aria-label="Paginación">
    <ul class="pagination pagination-sm mb-0">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">&lsaquo;</span>
            </li>
        @else
            <li class="page-item">
                <button type="button" class="page-link"
                    dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled">&lsaquo;</button>
            </li>
        @endif

        {{-- Páginas --}}
        @foreach ($display as $item)
            @if ($item === '...')
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">...</span>
                </li>
            @elseif ($item == $currentPage)
                <li class="page-item active" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $item }}" aria-current="page">
                    <span class="page-link">{{ $item }}</span>
                </li>
            @else
                <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $item }}">
                    <button type="button" class="page-link"
                        wire:click="gotoPage({{ $item }}, '{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}">{{ $item }}</button>
                </li>
            @endif
        @endforeach

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <button type="button" class="page-link"
                    dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled">&rsaquo;</button>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">&rsaquo;</span>
            </li>
        @endif
    </ul>
</nav>
@endif
