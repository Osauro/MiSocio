<div x-data x-on:actualizar-badge-compra.window="$wire.actualizarContador()">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($compraPendienteId): ?>
    <a href="<?php echo e(route('compra', ['compraId' => $compraPendienteId])); ?>" class="cart-icon-link position-relative" title="Compra en proceso">
        <i class="fa-solid fa-basket-shopping fa-lg"></i>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cantidadPendientes > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo e($cantidadPendientes); ?>

                <span class="visually-hidden">items en compra</span>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>
<?php else: ?>
    <a href="<?php echo e(route('compras')); ?>" class="cart-icon-link position-relative" title="Compras">
        <i class="fa-solid fa-basket-shopping fa-lg"></i>
    </a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\licos\resources\views/livewire/compra-cart.blade.php ENDPATH**/ ?>