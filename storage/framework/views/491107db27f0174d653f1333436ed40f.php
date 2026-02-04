<div style="display: contents;">
    <a href="<?php echo e(route('ventas')); ?>" class="cart-icon-link position-relative" title="Ventas">
        <i class="fa-solid fa-shopping-cart fa-lg"></i>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cantidadPendientes > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?php echo e($cantidadPendientes); ?>

                <span class="visually-hidden">ventas pendientes</span>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>
</div>
<?php /**PATH C:\laragon\www\licos\resources\views/livewire/venta-cart.blade.php ENDPATH**/ ?>