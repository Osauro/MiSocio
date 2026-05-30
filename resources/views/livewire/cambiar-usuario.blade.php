<div>
    @if($showOverlay)
    <div class="cu-overlay" wire:click="close">
        <div class="cu-content" wire:click.stop>

            <div class="cu-header">
                <h3><i class="fa-solid fa-users me-2"></i>Cambiar de Usuario</h3>
                <button type="button" class="btn-close-overlay" wire:click="close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @if(count($usuarios) === 0)
                <p class="text-center text-muted py-4">No hay otros usuarios en esta tienda.</p>
            @else
                <div class="cu-users-grid">
                    @foreach($usuarios as $u)
                    <button type="button" class="cu-user-btn"
                        wire:click="cambiarA({{ $u['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="cambiarA({{ $u['id'] }})">
                        <div class="cu-user-avatar-wrap">
                            <img src="{{ $u['photo'] }}" alt="{{ $u['name'] }}" class="cu-user-avatar">
                            <span class="cu-loading-spin" wire:loading wire:target="cambiarA({{ $u['id'] }})">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </span>
                        </div>
                        <span class="cu-user-name">{{ $u['name'] }}</span>
                        <span class="cu-user-role badge {{ $u['role'] === 'tenant' ? 'bg-primary' : 'bg-secondary' }}">
                            {{ $u['role'] === 'tenant' ? 'Admin' : 'Operador' }}
                        </span>
                    </button>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
    @endif

    <script>
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F2') {
                e.preventDefault();
                Livewire.dispatch('openCambiarUsuario');
            }
        });
    </script>

<style>
    .cu-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.25s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    .cu-content {
        background: #fff;
        border-radius: 16px;
        padding: 28px 32px;
        width: 90%;
        max-width: 560px;
        max-height: 85vh;
        overflow-y: auto;
        animation: slideUp 0.25s ease;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    @keyframes slideUp {
        from { transform: translateY(40px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    .cu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }

    .cu-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        color: #333;
    }

    .btn-close-overlay {
        background: transparent;
        border: none;
        font-size: 20px;
        color: #888;
        cursor: pointer;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .btn-close-overlay:hover { background: #f0f0f0; color: #333; }

    .cu-users-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 16px;
    }

    .cu-user-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 16px 10px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    .cu-user-btn:hover {
        border-color: var(--theme-deafult, #7366ff);
        background: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    .cu-user-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .cu-user-avatar-wrap {
        position: relative;
        width: 64px;
        height: 64px;
    }

    .cu-user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e0e0e0;
    }

    .cu-loading-spin {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.7);
        border-radius: 50%;
        font-size: 20px;
        color: var(--theme-deafult, #7366ff);
    }

    .cu-user-name {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        text-align: center;
        line-height: 1.2;
    }

    .cu-user-role { font-size: 11px; }
</style>
</div>
