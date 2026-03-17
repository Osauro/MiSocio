{{--
    Barra de paginacion fija en la parte inferior de la pagina.
    Uso: @include('partials.paginate-bar', ['results' => $variable, 'storageKey' => 'ventas'])

    - storageKey: nombre en camelCase que se usará para localStorage y cookie.
      cookie  → paginate{Ucfirst(storageKey)}  ej: paginateVentas
      localStorage → paginate{Ucfirst(storageKey)}  ej: paginateVentas
    - El componente Livewire debe tener la propiedad pública $perPage y leerla en mount() desde la cookie.
--}}
<div class="paginate-bar-fixed" x-data="{
    perPage: {{ $results->perPage() }},
    lsKey: 'paginate{{ ucfirst($storageKey) }}',
    cookieKey: 'paginate{{ ucfirst($storageKey) }}',
    init() {
        const saved = localStorage.getItem(this.lsKey);
        if (saved) {
            const val = parseInt(saved);
            if (val && val > 0) {
                document.cookie = `${this.cookieKey}=${val}; path=/; max-age=31536000; SameSite=Lax`;
                if (val !== this.perPage) {
                    this.perPage = val;
                    $wire.set('perPage', val);
                }
            }
        }
    },
    applyInput(el) {
        const val = parseInt(el.value);
        if (val && val > 0) {
            this.perPage = val;
            localStorage.setItem(this.lsKey, String(val));
            document.cookie = `${this.cookieKey}=${val}; path=/; max-age=31536000; SameSite=Lax`;
            $wire.set('perPage', val);
        } else {
            el.value = this.perPage;
        }
    }
}">

    <div class="paginate-bar-left">
        <p class="mb-0">Created By <a target="_blank" href="https://dieguitosoft.com">DieguitoSoft.com</a></p>
    </div>

    <div class="paginate-bar-right">
        {{-- Input registros por página --}}
        <input type="text" x-bind:value="perPage" @click="$event.target.select()"
            @keydown.enter="applyInput($event.target); $event.target.blur()" @blur="applyInput($event.target)"
            class="paginate-bar-input" title="Registros por página">

        {{-- Paginación compacta: < [página]/[total] > --}}
        @if($results->lastPage() > 1)
        <div class="paginate-nav" x-data="{
            current: {{ $results->currentPage() }},
            last: {{ $results->lastPage() }},
            goToPage(val) {
                const p = parseInt(val);
                if (p >= 1 && p <= this.last && p !== this.current) {
                    $wire.gotoPage(p);
                } else {
                    this.current = {{ $results->currentPage() }};
                }
            }
        }">
            {{-- Anterior --}}
            @if($results->onFirstPage())
                <button class="paginate-btn" disabled><i class="fa-solid fa-chevron-left"></i></button>
            @else
                <button class="paginate-btn" wire:click="previousPage" wire:loading.attr="disabled"><i class="fa-solid fa-chevron-left"></i></button>
            @endif

            {{-- Input página actual --}}
            <input type="number"
                x-model="current"
                @focus="$event.target.select()"
                @keydown.enter="goToPage($event.target.value); $event.target.blur()"
                @blur="goToPage($event.target.value)"
                class="paginate-page-input"
                min="1" max="{{ $results->lastPage() }}"
                title="Ir a página">

            <span class="paginate-separator">/</span>

            {{-- Total páginas (solo lectura) --}}
            <input type="text" value="{{ $results->lastPage() }}" readonly class="paginate-page-input paginate-total" title="Total de páginas">

            {{-- Siguiente --}}
            @if($results->hasMorePages())
                <button class="paginate-btn" wire:click="nextPage" wire:loading.attr="disabled"><i class="fa-solid fa-chevron-right"></i></button>
            @else
                <button class="paginate-btn" disabled><i class="fa-solid fa-chevron-right"></i></button>
            @endif
        </div>
        @endif
    </div>
</div>

<style>
    .footer-wrapper {
        display: none !important;
    }

    /* El card:hover del tema tiene transform: translateY(-2px) que rompe position:fixed */
    .paginate-bar-fixed {
        position: fixed !important;
        bottom: 0 !important;
        left: 0;
        right: 0;
        top: auto !important;
        z-index: 1050;
        background: #fff;
        border-top: 2px solid #e0e6ed;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.10);
        height: 52px;
        min-height: 52px;
        max-height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        overflow: hidden;
        transform: translateZ(0) !important;
        will-change: transform;
    }

    .paginate-bar-left p {
        font-size: 0.8rem;
        color: #555;
        margin: 0;
    }

    .paginate-bar-left a {
        color: #555;
        text-decoration: none;
    }

    .paginate-bar-left a:hover {
        color: #1b55e2;
    }

    .paginate-bar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    /* Input registros por página */
    .paginate-bar-input {
        width: 50px;
        height: 30px;
        text-align: center;
        font-size: 0.85rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0 4px;
        color: #333;
        background: #fff;
    }

    .paginate-bar-input:focus {
        outline: none;
        border-color: #1b55e2;
        box-shadow: 0 0 0 2px rgba(27, 85, 226, 0.15);
    }

    /* Navegación compacta */
    .paginate-nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .paginate-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.7rem;
        color: #333;
        transition: background-color 0.15s, color 0.15s;
    }

    .paginate-btn:hover:not(:disabled) {
        background: #1b55e2;
        color: #fff;
        border-color: #1b55e2;
    }

    .paginate-btn:disabled {
        opacity: 0.4;
        cursor: default;
    }

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
        /* Ocultar flechas spinner en number input */
        -moz-appearance: textfield;
    }

    .paginate-page-input::-webkit-inner-spin-button,
    .paginate-page-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .paginate-page-input:focus {
        outline: none;
        border-color: #1b55e2;
        box-shadow: 0 0 0 2px rgba(27, 85, 226, 0.15);
    }

    .paginate-total {
        background: #f8f9fa;
        cursor: default;
        color: #666;
    }

    .paginate-separator {
        font-size: 0.9rem;
        color: #666;
        line-height: 1;
    }

    /* Mobile */
    @media (max-width: 767px) {
        .paginate-bar-left {
            display: none !important;
        }

        .paginate-bar-right {
            width: 100%;
            justify-content: flex-end;
        }

        .paginate-bar-input {
            width: 40px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="paginate-bar-fixed" x-data="{
    perPage: {{ $results->perPage() }},
    lsKey: 'paginate{{ ucfirst($storageKey) }}',
    cookieKey: 'paginate{{ ucfirst($storageKey) }}',
    init() {
        const saved = localStorage.getItem(this.lsKey);
        if (saved) {
            const val = parseInt(saved);
            if (val && val > 0) {
                // Sincronizar cookie para que el servidor la lea en el próximo mount()
                document.cookie = `${this.cookieKey}=${val}; path=/; max-age=31536000; SameSite=Lax`;
                if (val !== this.perPage) {
                    this.perPage = val;
                    $wire.set('perPage', val);
                }
            }
        }
    },
    applyInput(el) {
        const val = parseInt(el.value);
        if (val && val > 0) {
            this.perPage = val;
            localStorage.setItem(this.lsKey, String(val));
            document.cookie = `${this.cookieKey}=${val}; path=/; max-age=31536000; SameSite=Lax`;
            $wire.set('perPage', val);
        } else {
            el.value = this.perPage;
        }
    }
}">

    <div class="paginate-bar-left">
        <p class="mb-0">Created By <a target="_blank" href="https://dieguitosoft.com">DieguitoSoft.com</a></p>
    </div>

    <div class="paginate-bar-right">
        <input type="text" x-bind:value="perPage" @click="$event.target.select()"
            @keydown.enter="applyInput($event.target); $event.target.blur()" @blur="applyInput($event.target)"
            class="paginate-bar-input" title="Registros por página">
        <div class="paginate-bar-links">
            {{ $results->links() }}
        </div>
    </div>
</div>

<style>
    .footer-wrapper {
        display: none !important;
    }

    /* El card:hover del tema tiene transform: translateY(-2px) que rompe position:fixed */
    .paginate-bar-fixed {
        position: fixed !important;
        bottom: 0 !important;
        left: 0;
        right: 0;
        top: auto !important;
        z-index: 1050;
        background: #fff;
        border-top: 2px solid #e0e6ed;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.10);
        height: 52px;
        min-height: 52px;
        max-height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        overflow: hidden;
        transform: translateZ(0) !important;
        will-change: transform;
    }

    .paginate-bar-left p {
        font-size: 0.8rem;
        color: #555;
        margin: 0;
    }

    .paginate-bar-left a {
        color: #555;
        text-decoration: none;
    }

    .paginate-bar-left a:hover {
        color: #1b55e2;
    }

    .paginate-bar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .paginate-bar-input {
        width: 50px;
        height: 30px;
        text-align: center;
        font-size: 0.85rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0 4px;
        color: #333;
        background: #fff;
    }

    .paginate-bar-input:focus {
        outline: none;
        border-color: #1b55e2;
        box-shadow: 0 0 0 2px rgba(27, 85, 226, 0.15);
    }

    /* Bootstrap pagination */
    .paginate-bar-links nav {
        margin: 0;
    }

    .paginate-bar-links .pagination {
        margin: 0 !important;
        flex-wrap: nowrap;
        font-size: 0.85rem;
    }

    .paginate-bar-links .pagination .page-item {
        height: 32px;
    }

    .paginate-bar-links .pagination .page-item .page-link {
        padding: 5px 10px;
        line-height: 1.2;
        height: 32px;
        box-sizing: border-box;
        border-color: #dee2e6 !important;
        box-shadow: none !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    /* Mobile: input a la izquierda, paginado a la derecha */
    @media (max-width: 767px) {
        .paginate-bar-left {
            display: none !important;
        }

        .paginate-bar-right {
            width: 100%;
            justify-content: space-between;
        }

        .paginate-bar-input {
            width: 40px;
            font-size: 0.8rem;
        }

        .paginate-bar-links .pagination .page-item .page-link {
            padding: 5px 11px;
            font-size: 0.85rem;
        }
    }
</style>
