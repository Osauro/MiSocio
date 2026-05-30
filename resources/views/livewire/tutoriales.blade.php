<div class="container-fluid py-3">

    {{-- Encabezado --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fa-brands fa-youtube text-danger me-2"></i>
                Videotutoriales
            </h4>
            <small class="text-muted">Aprende a usar MiSocio paso a paso</small>
        </div>
        <button wire:click="refrescarLista" class="btn btn-outline-secondary btn-sm"
            wire:loading.attr="disabled" title="Actualizar lista de videos">
            <span wire:loading.remove wire:target="refrescarLista">
                <i class="fa-solid fa-rotate me-1"></i> Actualizar
            </span>
            <span wire:loading wire:target="refrescarLista">
                <i class="fa-solid fa-spinner fa-spin me-1"></i> Cargando...
            </span>
        </button>
    </div>

    @if(empty($videos))
        <div class="alert alert-warning">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            No se pudieron cargar los videos. Verifica tu conexión a Internet o intenta actualizar.
        </div>
    @else
        <div class="row g-3">

            {{-- ── Player principal ── --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            <iframe
                                src="https://www.youtube.com/embed/{{ $videoActivo }}?autoplay=0&rel=0"
                                title="{{ $tituloActivo }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                class="rounded-top"
                                style="border: none;">
                            </iframe>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top px-3 py-2">
                        <p class="mb-0 fw-semibold text-truncate">
                            <i class="fa-solid fa-play-circle text-danger me-1"></i>
                            {{ $tituloActivo }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── Lista de videos ── --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-2">
                        <span class="fw-semibold">
                            <i class="fa-solid fa-list-ul me-1 text-muted"></i>
                            Lista de videos
                            <span class="badge bg-secondary ms-1">{{ count($videos) }}</span>
                        </span>
                    </div>
                    <div class="card-body p-0" style="max-height: 520px; overflow-y: auto;">
                        @foreach($videos as $index => $video)
                            <div wire:click="seleccionarVideo('{{ $video['id'] }}', '{{ addslashes($video['titulo']) }}')"
                                class="d-flex align-items-start gap-2 px-3 py-2 cursor-pointer tutorial-item
                                    {{ $videoActivo === $video['id'] ? 'bg-danger bg-opacity-10 border-start border-danger border-3' : '' }}"
                                style="cursor: pointer; transition: background .15s;"
                                title="{{ $video['titulo'] }}">

                                {{-- Miniatura --}}
                                <div class="flex-shrink-0 position-relative" style="width: 100px;">
                                    <img src="{{ $video['thumbnail'] }}"
                                        alt="{{ $video['titulo'] }}"
                                        class="rounded"
                                        style="width: 100px; height: 56px; object-fit: cover;">
                                    @if($videoActivo === $video['id'])
                                        <span class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fa-solid fa-circle-play text-white fa-lg" style="text-shadow: 0 0 4px #000;"></i>
                                        </span>
                                    @else
                                        <span class="position-absolute top-50 start-50 translate-middle">
                                            <i class="fa-regular fa-circle-play text-white fa-lg" style="text-shadow: 0 0 4px #000; opacity: .7;"></i>
                                        </span>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="flex-grow-1 overflow-hidden">
                                    <p class="mb-0 small fw-semibold lh-sm"
                                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $index + 1 }}. {{ $video['titulo'] }}
                                    </p>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr class="my-0 mx-3">
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
