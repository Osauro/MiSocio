<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-0 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="d-none d-md-block mb-0">Préstamos de Envases</h3>
                            <div class="nav-item w-100 w-md-auto" style="max-width: 100%;">
                                <div class="input-group">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fecha_inicio && $fecha_fin): ?>
                                        <button type="button" class="btn btn-outline-danger"
                                            wire:click="limpiarFiltroFechas" title="Limpiar filtro de fechas">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro"
                                            title="Filtrar por fechas">
                                            <i class="fa-solid fa-calendar-days"></i>
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <input type="text" class="form-control text-start"
                                        placeholder="Buscar préstamo..." wire:model.live="search"
                                        style="min-width: 200px;" id="searchInput" autofocus>
                                    <button class="btn btn-primary" wire:click="crearPrestamo"><i
                                            class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador fijo para móvil -->
                    <div class="card-header card-no-border d-md-none"
                        style="position: sticky; top: 70px; z-index: 1030; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 12px; margin: 0;">
                        <div class="input-group">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fecha_inicio && $fecha_fin): ?>
                                <button type="button" class="btn btn-outline-danger" wire:click="limpiarFiltroFechas"
                                    title="Limpiar filtro de fechas">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro"
                                    title="Filtrar por fechas">
                                    <i class="fa-solid fa-calendar-days"></i>
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <input type="text" class="form-control text-start" placeholder="Buscar préstamo..."
                                wire:model.live="search" id="searchInputMobile" autofocus>
                            <button class="btn btn-primary" wire:click="crearPrestamo"><i
                                    class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="card-body transaction-history pt-0 mt-3 pb-2">
                        <div class="row g-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $prestamos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prestamo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php
                                    $estadoReal = $prestamo->estado_real;
                                ?>
                                <div class="col-md-4 col-12" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('prestamo-{{ $prestamo->id }}', get_defined_vars()); ?>wire:key="prestamo-<?php echo e($prestamo->id); ?>">
                                    <div
                                        class="card mb-0 shadow-sm <?php echo e($estadoReal === 'Devuelto' ? 'opacity-75' : ($estadoReal === 'Vencido' ? 'border-danger' : '')); ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Header: [titulo][botones] -->
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h4 class="mb-0 fw-bold">
                                                            Préstamo #<?php echo e($prestamo->numero_folio); ?>

                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($estadoReal === 'Pendiente'): ?>
                                                                <a href="<?php echo e(route('prestamo', ['prestamoId' => $prestamo->id])); ?>"
                                                                    class="btn btn-sm btn-warning"
                                                                    title="Continuar préstamo">
                                                                    <i class="fa-solid fa-arrow-right"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-info"
                                                                    wire:click="verDetalles(<?php echo e($prestamo->id); ?>)"
                                                                    title="Ver detalles">
                                                                    <i class="fa-solid fa-eye"></i>
                                                                </button>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Items: Avatar Group de productos -->
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prestamo->prestamoItems->count() > 0): ?>
                                                        <div class="avatar-group mb-3">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $prestamo->prestamoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                                <div class="avatar" style="cursor: pointer;"
                                                                    x-on:click="$dispatch('mostrarKardex', { productoId: <?php echo e($item->producto_id); ?> })"
                                                                    title="<?php echo e($item->producto->nombre ?? 'Producto'); ?> - Clic para ver Kardex">
                                                                    <img src="<?php echo e($item->producto->photo_url ?? ''); ?>"
                                                                        alt="<?php echo e($item->producto->nombre ?? 'Producto'); ?>">
                                                                    <span
                                                                        class="quantity-badge"><?php echo e($item->cantidad); ?></span>
                                                                </div>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                        </div>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                    <!-- Monto y Vencimiento -->
                                                    <div class="d-flex gap-2 flex-wrap mb-2">
                                                        <span class="badge bg-primary">
                                                            <i class="fa-solid fa-coins me-1"></i>
                                                            Bs. <?php echo e(number_format($prestamo->deposito, 2)); ?>

                                                        </span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prestamo->fecha_vencimiento): ?>
                                                            <span
                                                                class="badge <?php echo e($estadoReal === 'Vencido' ? 'bg-danger' : ($estadoReal === 'Devuelto' ? 'bg-success' : 'bg-warning text-dark')); ?>">
                                                                <i class="fa-solid fa-calendar-check me-1"></i>
                                                                <?php echo e($prestamo->fecha_vencimiento->format('d/m/Y')); ?>

                                                            </span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <span
                                                            class="badge <?php echo e($estadoReal === 'Devuelto' ? 'bg-success' : ($estadoReal === 'Vencido' ? 'bg-danger' : ($estadoReal === 'Prestado' ? 'bg-info' : 'bg-secondary'))); ?>">
                                                            <?php echo e($estadoReal); ?>

                                                        </span>
                                                    </div>

                                                    <!-- Footer: [usuario][fecha][cliente] -->
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-muted flex-wrap gap-1">
                                                        <small>
                                                            <i
                                                                class="fa-solid fa-user-tie me-1"></i><?php echo e($prestamo->user->name ?? 'Usuario'); ?>

                                                        </small>
                                                        <small>
                                                            <i
                                                                class="fa-solid fa-calendar me-1"></i><?php echo e($prestamo->created_at->format('d/m/Y H:i')); ?>

                                                        </small>
                                                        <small>
                                                            <i
                                                                class="fa-solid fa-user me-1"></i><?php echo e($prestamo->cliente->nombre ?? 'Sin cliente'); ?>

                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-handshake fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron préstamos</p>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer fijo con paginado -->
    <footer class="fixed-footer shadow-sm py-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted d-none d-md-block">Created By <a href="https://dieguitosoft.com"
                        target="_blank">DieguitoSoft.com</a></small>
                <div class="d-flex align-items-center gap-2">
                    <div x-data="{
                        init() {
                            const saved = localStorage.getItem('paginatePrestamos') || document.cookie.split('; ').find(row => row.startsWith('paginateCompras='))?.split('=')[1];
                            if (saved) {
                                $wire.set('perPage', parseInt(saved));
                            }
                        }
                    }">
                        <input type="number" class="form-control form-control-sm text-center" style="width: 60px;"
                            wire:model.live="perPage" min="1" max="100" title="Registros por página"
                            onfocus="this.select()"
                            @input="
                                   localStorage.setItem('paginatePrestamos', $event.target.value);
                                   document.cookie = 'paginatePrestamos=' + $event.target.value + '; path=/; max-age=31536000';
                               ">
                    </div>
                    <?php echo e($prestamos->links()); ?>

                </div>
            </div>
        </div>
    </footer>

    <!-- Modal de Filtro de Fechas -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModalFiltro): ?>
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Filtrar por Fechas</h5>
                        <button type="button" class="btn-close" wire:click="cerrarModalFiltro"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Desde</label>
                                <input type="date" class="form-control" wire:model.live="fecha_inicio"
                                    <?php if($fecha_fin): ?> max="<?php echo e($fecha_fin); ?>" <?php endif; ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hasta</label>
                                <input type="date" class="form-control" wire:model.live="fecha_fin"
                                    <?php if($fecha_inicio): ?> min="<?php echo e($fecha_inicio); ?>" <?php endif; ?>
                                    <?php if(!$fecha_inicio): ?> disabled <?php endif; ?>>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="cerrarModalFiltro">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Modal de Detalles de Préstamo -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModal && $prestamoSeleccionado): ?>
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-handshake me-2"></i>Préstamo
                            #<?php echo e($prestamoSeleccionado->numero_folio); ?>

                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        <!-- Tabla de productos -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="align-middle">Producto</th>
                                        <th class="text-center align-middle">Cantidad</th>
                                        <th class="text-end align-middle">Precio</th>
                                        <th class="text-end align-middle">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $prestamoSeleccionado->prestamoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr style="cursor: pointer;"
                                            x-on:click="$dispatch('mostrarKardex', { productoId: <?php echo e($item->producto_id); ?> })"
                                            title="Clic para ver movimientos de Kardex">
                                            <td class="align-middle text-truncate">
                                                <strong><?php echo e($item->producto->nombre ?? 'Producto'); ?></strong>
                                            </td>
                                            <td class="text-center align-middle text-truncate">
                                                <span class="badge bg-info text-dark"><?php echo e($item->cantidad); ?></span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">Bs.
                                                <?php echo e(number_format($item->precio, 2)); ?></td>
                                            <td class="text-end align-middle text-truncate">
                                                <strong>Bs. <?php echo e(number_format($item->subtotal, 2)); ?></strong>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end align-middle text-truncate">
                                            <strong>Depósito:</strong>
                                        </td>
                                        <td class="text-end align-middle text-truncate">
                                            <strong class="text-primary fs-5">
                                                Bs. <?php echo e(number_format($prestamoSeleccionado->deposito, 2)); ?>

                                            </strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user-tie me-1"></i><?php echo e($prestamoSeleccionado->user->name ?? 'Usuario'); ?>

                            </small>
                            <small class="text-muted">
                                <i
                                    class="fa-solid fa-user me-1"></i><?php echo e($prestamoSeleccionado->cliente->nombre ?? 'Sin cliente'); ?>

                            </small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prestamoSeleccionado->fecha_vencimiento): ?>
                                <small class="text-muted">
                                    <i
                                        class="fa-solid fa-calendar-check me-1"></i><?php echo e($prestamoSeleccionado->fecha_vencimiento->format('d/m/Y')); ?>

                                </small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prestamoSeleccionado->estado === 'Prestado'): ?>
                                <button type="button" class="btn btn-success" wire:click="procesarDevolucion"
                                    wire:loading.attr="disabled" <?php if($procesandoDevolucion): ?> disabled <?php endif; ?>>
                                    <span wire:loading.remove wire:target="procesarDevolucion">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Devolver
                                    </span>
                                    <span wire:loading wire:target="procesarDevolucion">
                                        <i class="fa-solid fa-spinner fa-spin me-1"></i>Procesando...
                                    </span>
                                </button>
                            <?php else: ?>
                                <span
                                    class="badge bg-<?php echo e($prestamoSeleccionado->estado === 'Devuelto' ? 'success' : 'secondary'); ?> fs-6">
                                    <?php echo e($prestamoSeleccionado->estado); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '3060809928-0';
        ob_start();
    ?>
        <script>
            // Gestionar el estado del body cuando hay modales abiertos
            $wire.on('$refresh', () => {
                if ($wire.mostrarModal || $wire.mostrarModalFiltro) {
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '0px';
                } else {
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            });

            // Cerrar modal al hacer clic fuera del contenido
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-backdrop') ||
                    (e.target.classList.contains('modal') && e.target.classList.contains('show'))) {
                    if ($wire.mostrarModal) {
                        $wire.call('cerrarModal');
                    } else if ($wire.mostrarModalFiltro) {
                        $wire.call('cerrarModalFiltro');
                    }
                }
            });

            // SweetAlert para toasts
            $wire.on('alert', (event) => {
                const data = event[0] || event;
                Swal.fire({
                    title: data.type === 'success' ? '¡Éxito!' : 'Error',
                    text: data.message,
                    icon: data.type,
                    confirmButtonColor: data.type === 'success' ? '#28a745' : '#d33',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>

    <!-- Componente anidado de Kardex Modal -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('kardex-modal', []);

$key = null;
$__componentSlots = [];

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3060809928-0', $key);

$__html = app('livewire')->mount($__name, $__params, $key, $__componentSlots);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php /**PATH C:\laragon\www\licos\resources\views/livewire/prestamos.blade.php ENDPATH**/ ?>