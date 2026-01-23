<div x-show="$store.loading.show"
     x-cloak
     class="loading-overlay"
     style="display: none;">
    <div class="spinner-container">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-3 text-white fw-bold" x-text="$store.loading.message || 'Cargando...'"></p>
    </div>
</div>
