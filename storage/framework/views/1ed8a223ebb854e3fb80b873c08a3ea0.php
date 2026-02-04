<div style="display: contents;">
    <a href="<?php echo e(route('prestamos')); ?>" class="cart-icon-link position-relative" title="Préstamos">
        <i class="fa-solid fa-handshake fa-lg"></i>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cantidadItems > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                <?php echo e($cantidadItems); ?>

                <span class="visually-hidden">items en préstamo</span>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>
</div>
<?php /**PATH C:\laragon\www\licos\resources\views/livewire/prestamo-cart.blade.php ENDPATH**/ ?>