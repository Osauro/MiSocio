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

            @if(!$usuarioSeleccionado)
                {{-- Paso 1: elegir usuario --}}
                @if(count($usuarios) === 0)
                    <p class="text-center text-muted py-4">No hay otros usuarios en esta tienda.</p>
                @else
                    <div class="cu-users-grid" style="--count: {{ count($usuarios) }}">
                        @foreach($usuarios as $u)
                        <button type="button" class="cu-user-btn" wire:click="seleccionarUsuario({{ $u['id'] }})">
                            <img src="{{ $u['photo'] }}" alt="{{ $u['name'] }}" class="cu-user-avatar">
                            <span class="cu-user-name">{{ $u['name'] }}</span>
                            <span class="cu-user-role badge {{ $u['role'] === 'tenant' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $u['role'] === 'tenant' ? 'Admin' : 'Operador' }}
                            </span>
                        </button>
                        @endforeach
                    </div>
                @endif
            @else
                {{-- Paso 2: ingresar PIN --}}
                @php $usuario = collect($usuarios)->firstWhere('id', $usuarioSeleccionado); @endphp
                <div class="cu-pin-step">
                    <button type="button" class="btn btn-link cu-back-btn" wire:click="$set('usuarioSeleccionado', null)">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </button>

                    <div class="cu-pin-user">
                        <img src="{{ $usuario['photo'] }}" alt="{{ $usuario['name'] }}" class="cu-pin-avatar">
                        <div>
                            <div class="cu-pin-name">{{ $usuario['name'] }}</div>
                            <span class="badge {{ $usuario['role'] === 'tenant' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $usuario['role'] === 'tenant' ? 'Admin' : 'Operador' }}
                            </span>
                        </div>
                    </div>

                    <form wire:submit="confirmar" class="cu-pin-form">
                        <label class="cu-pin-label">Ingresa tu PIN</label>
                        <input
                            id="cu-pin-input"
                            type="password"
                            inputmode="numeric"
                            maxlength="4"
                            pattern="\d{4}"
                            autocomplete="off"
                            wire:model="pin"
                            class="cu-pin-input {{ $error ? 'is-invalid' : '' }}"
                            placeholder="••••"
                        >
                        @if($error)
                            <div class="cu-pin-error">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $error }}
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary cu-pin-submit">
                            <span wire:loading wire:target="confirmar">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i>
                            </span>
                            <span wire:loading.remove wire:target="confirmar">
                                <i class="fa-solid fa-right-to-bracket me-1"></i>
                            </span>
                            Ingresar
                        </button>
                    </form>
                </div>
            @endif

        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('focusPinInput', () => {
            setTimeout(() => {
                const input = document.getElementById('cu-pin-input');
                if (input) input.focus();
            }, 80);
        });
    });

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

    /* Grid de usuarios */
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
    }
    .cu-user-btn:hover {
        border-color: var(--theme-deafult, #7366ff);
        background: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }

    .cu-user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e0e0e0;
    }

    .cu-user-name {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        text-align: center;
        line-height: 1.2;
    }

    .cu-user-role {
        font-size: 11px;
    }

    /* Paso PIN */
    .cu-pin-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .cu-back-btn {
        align-self: flex-start;
        padding: 0;
        color: #666;
        font-size: 14px;
    }

    .cu-pin-user {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .cu-pin-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--theme-deafult, #7366ff);
    }

    .cu-pin-name {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        margin-bottom: 4px;
    }

    .cu-pin-form {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        width: 100%;
        max-width: 240px;
    }

    .cu-pin-label {
        font-size: 14px;
        color: #666;
        margin: 0;
    }

    .cu-pin-input {
        width: 100%;
        text-align: center;
        font-size: 28px;
        letter-spacing: 10px;
        padding: 10px 16px;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        outline: none;
        transition: border-color 0.2s;
    }
    .cu-pin-input:focus {
        border-color: var(--theme-deafult, #7366ff);
    }
    .cu-pin-input.is-invalid {
        border-color: #dc3545;
    }

    .cu-pin-error {
        color: #dc3545;
        font-size: 13px;
        text-align: center;
    }

    .cu-pin-submit {
        width: 100%;
        padding: 10px;
        font-size: 15px;
        border-radius: 8px;
    }
</style>
