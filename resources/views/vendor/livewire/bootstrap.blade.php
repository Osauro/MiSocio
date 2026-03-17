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
<nav aria-label="Paginación" class="d-flex justify-content-end">
    <div style="display:flex;align-items:center;gap:4px;"
         x-data="{
            current: {{ $paginator->currentPage() }},
            last: {{ $paginator->lastPage() }},
            goToPage(val) {
                const p = parseInt(val);
                if (p >= 1 && p <= this.last && p !== {{ $paginator->currentPage() }}) {
                    $wire.gotoPage(p, '{{ $paginator->getPageName() }}');
                } else {
                    this.current = {{ $paginator->currentPage() }};
                }
            }
         }">

        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <button class="paginate-btn" disabled><i class="fa-solid fa-chevron-left" style="font-size:0.7rem;"></i></button>
        @else
            <button class="paginate-btn"
                wire:click="previousPage('{{ $paginator->getPageName() }}')"
                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                wire:loading.attr="disabled">
                <i class="fa-solid fa-chevron-left" style="font-size:0.7rem;"></i>
            </button>
        @endif

        {{-- Input página actual --}}
        <input type="number"
            x-model="current"
            @focus="$event.target.select()"
            @keydown.enter="goToPage($event.target.value); $event.target.blur()"
            @blur="goToPage($event.target.value)"
            class="paginate-page-input"
            min="1" max="{{ $paginator->lastPage() }}"
            title="Ir a página">

        <span style="font-size:0.9rem;color:#666;line-height:1;">/</span>

        {{-- Total páginas (solo lectura) --}}
        <input type="text" value="{{ $paginator->lastPage() }}" readonly class="paginate-page-input paginate-total" title="Total de páginas">

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <button class="paginate-btn"
                wire:click="nextPage('{{ $paginator->getPageName() }}')"
                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                wire:loading.attr="disabled">
                <i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i>
            </button>
        @else
            <button class="paginate-btn" disabled><i class="fa-solid fa-chevron-right" style="font-size:0.7rem;"></i></button>
        @endif
    </div>
</nav>

<style>
    .paginate-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        cursor: pointer;
        color: #333;
        transition: background-color 0.15s, color 0.15s;
    }
    .paginate-btn:hover:not(:disabled) {
        background: #1b55e2;
        color: #fff;
        border-color: #1b55e2;
    }
    .paginate-btn:disabled { opacity: 0.4; cursor: default; }
    .paginate-page-input {
        width: 44px;
        height: 30px;
        text-align: center;
        font-size: 0.85rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0 4px;
        color: #333;
        background: #fff;
        -moz-appearance: textfield;
    }
    .paginate-page-input::-webkit-inner-spin-button,
    .paginate-page-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .paginate-page-input:focus {
        outline: none;
        border-color: #1b55e2;
        box-shadow: 0 0 0 2px rgba(27,85,226,0.15);
    }
    .paginate-total { background: #f8f9fa; cursor: default; color: #666; }
</style>
@endif

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
