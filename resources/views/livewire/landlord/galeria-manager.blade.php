<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">

                    <!-- Header escritorio -->
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">
                                <i class="fa-solid fa-images me-2"></i>
                                Galería de Imágenes
                            </h3>
                            <div class="d-flex gap-2 w-100 w-md-auto">
                                <div class="input-group" style="min-width: 220px;">
                                    <input type="text" class="form-control" placeholder="Buscar imagen..."
                                        wire:model.live="search">
                                </div>
                                <label class="btn btn-primary mb-0" style="cursor:pointer;" title="Subir imagen">
                                    <i class="fa-solid fa-upload"></i>
                                    <input type="file" wire:model="nuevaImagen" accept="image/*" class="d-none">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none"
                        style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" placeholder="Buscar imagen..."
                                wire:model.live="search">
                            <label class="btn btn-primary mb-0" style="cursor:pointer;">
                                <i class="fa-solid fa-upload"></i>
                                <input type="file" wire:model="nuevaImagen" accept="image/*" class="d-none">
                            </label>
                        </div>
                    </div>

                    <div class="card-body pt-3 pb-4">

                        @error('nuevaImagen')
                            <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                        @enderror

                        <div class="row g-3">
                            @forelse($imagenes as $imagen)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card h-100 shadow-sm position-relative">

                                        <!-- Área de imagen clicable para lightbox -->
                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                            style="height: 150px; cursor: zoom-in; overflow: hidden; border-radius: 4px 4px 0 0;"
                                            onclick="abrirLightbox('{{ asset('storage/' . $imagen->url) }}', '{{ $imagen->nombre ?? basename($imagen->url) }}')">
                                            <img src="{{ asset('storage/' . $imagen->url) }}"
                                                alt="{{ $imagen->nombre ?? 'Imagen' }}"
                                                style="max-width: 100%; max-height: 150px; object-fit: contain;">
                                        </div>

                                        <!-- Badge uso -->
                                        <span class="badge bg-secondary position-absolute top-0 start-0 m-1"
                                            title="Veces usado en productos">
                                            <i class="fa-solid fa-box me-1"></i>{{ $imagen->veces_usado }}
                                        </span>

                                        <!-- Botón eliminar -->
                                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                            wire:click="confirmarEliminar({{ $imagen->id }})"
                                            title="Eliminar imagen">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>

                                        <!-- Info + botón editar nombre -->
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center gap-1">
                                                <p class="small text-muted mb-0 text-truncate flex-grow-1" title="{{ $imagen->nombre ?? basename($imagen->url) }}">
                                                    {{ $imagen->nombre ?? '—' }}
                                                </p>
                                                <button class="btn btn-sm p-0 text-secondary flex-shrink-0"
                                                    wire:click="editarNombre({{ $imagen->id }})"
                                                    title="Editar nombre">
                                                    <i class="fa-solid fa-pen fa-xs"></i>
                                                </button>
                                            </div>
                                            <p class="small text-muted mb-0">
                                                {{ $imagen->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-images fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No se encontraron imágenes en la galería.</p>
                                    <label class="btn btn-primary" style="cursor:pointer;">
                                        <i class="fa-solid fa-upload me-2"></i> Subir primera imagen
                                        <input type="file" wire:model="nuevaImagen" accept="image/*" class="d-none">
                                    </label>
                                </div>
                            @endforelse
                        </div><!-- /.row -->

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay de carga al subir imagen (controlado por JS) -->
    <div id="upload-overlay" style="display:none; position:fixed; inset:0; background:rgba(255,255,255,0.75); z-index:9998; flex-direction:column; align-items:center; justify-content:center;">
        <div class="spinner-border text-primary" style="width:3rem; height:3rem;" role="status"></div>
        <p class="mt-3 fw-semibold text-primary">Subiendo imagen...</p>
    </div>

    <!-- Footer fijo con paginado -->
    <footer class="fixed-footer shadow-sm py-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted d-none d-md-block">Created By <a href="https://dieguitosoft.com" target="_blank">DieguitoSoft.com</a></small>
                <div class="d-flex align-items-center gap-2" x-data="{
                    init() {
                        const saved = localStorage.getItem('paginateGaleria') || document.cookie.split('; ').find(row => row.startsWith('paginateGaleria='))?.split('=')[1];
                        if (saved) {
                            $wire.set('perPage', parseInt(saved));
                        }
                    }
                }">
                    <input type="number"
                           class="form-control form-control-sm text-center"
                           style="width: 60px;"
                           wire:model.live="perPage"
                           min="1"
                           max="200"
                           title="Registros por página"
                           onfocus="this.select()"
                           @input="
                               localStorage.setItem('paginateGaleria', $event.target.value);
                               document.cookie = 'paginateGaleria=' + $event.target.value + '; path=/; max-age=31536000';
                           ">
                    {{ $imagenes->links() }}
                </div>
            </div>
        </div>
    </footer>

    <!-- Lightbox overlay -->
    <div id="lightbox-overlay"
         onclick="cerrarLightbox()"
         style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:9999; cursor:zoom-out; align-items:center; justify-content:center;">
        <button onclick="cerrarLightbox()" style="position:absolute; top:16px; right:20px; background:none; border:none; color:#fff; font-size:2rem; cursor:pointer; line-height:1;">&times;</button>
        <img id="lightbox-img" src="" alt=""
             style="max-width:90vw; max-height:90vh; object-fit:contain; border-radius:6px; box-shadow:0 4px 30px rgba(0,0,0,0.5);"
             onclick="event.stopPropagation()">
    </div>

    <script>
        function abrirLightbox(src, alt) {
            const overlay = document.getElementById('lightbox-overlay');
            const img = document.getElementById('lightbox-img');
            img.src = src;
            img.alt = alt;
            overlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function cerrarLightbox() {
            document.getElementById('lightbox-overlay').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarLightbox(); });

        // Mostrar spinner al seleccionar archivo
        document.addEventListener('change', function (e) {
            if (e.target && e.target.type === 'file' && e.target.getAttribute('wire:model') === 'nuevaImagen') {
                if (e.target.files && e.target.files.length > 0) {
                    const overlay = document.getElementById('upload-overlay');
                    if (overlay) overlay.style.display = 'flex';
                }
            }
        }, true);

        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(() => {
                    const overlay = document.getElementById('upload-overlay');
                    if (overlay) overlay.style.display = 'none';
                });
                fail(() => {
                    const overlay = document.getElementById('upload-overlay');
                    if (overlay) overlay.style.display = 'none';
                });
            });
            Livewire.on('swal:pedir-nombre', (data) => {
                const id = data[0]?.id ?? data.id;
                Swal.fire({
                    title: 'Nombre de la imagen',
                    input: 'text',
                    inputPlaceholder: 'Escribe un nombre descriptivo...',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Omitir',
                    confirmButtonColor: '#198754',
                    inputAttributes: { autocomplete: 'off' },
                }).then((result) => {
                    if (result.isConfirmed && result.value.trim() !== '') {
                        Livewire.dispatch('guardarNombreImagen', { id: id, nombre: result.value.trim() });
                    }
                });
            });
        });
    </script>
</div>
