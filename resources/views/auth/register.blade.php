<x-guest-layout>
    <h1 class="auth-title">Registrarse</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="form-label">Nombre Completo</label>
            <input
                id="name"
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Ingresa tu nombre completo"
            />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Celular -->
        <div class="mb-4">
            <label for="celular" class="form-label">Celular</label>
            <input
                id="celular"
                type="text"
                class="form-control @error('celular') is-invalid @enderror"
                name="celular"
                value="{{ old('celular') }}"
                required
                autocomplete="tel"
                maxlength="8"
                placeholder="Número de celular (8 dígitos)"
            />
            @error('celular')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password (PIN) -->
        <div class="mb-4">
            <label for="password" class="form-label">PIN (4 dígitos)</label>
            <input
                id="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                required
                autocomplete="new-password"
                maxlength="4"
                inputmode="numeric"
                placeholder="Crea un PIN de 4 dígitos"
            />
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-5">
            <label for="password_confirmation" class="form-label">Confirmar PIN</label>
            <input
                id="password_confirmation"
                type="password"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                name="password_confirmation"
                required
                autocomplete="new-password"
                maxlength="4"
                inputmode="numeric"
                placeholder="Confirma tu PIN"
            />
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="mb-4">
            <button type="submit" class="btn-primary-custom">
                Registrarse
            </button>
        </div>

        <!-- Login Link -->
        <div class="text-center mt-5">
            <span class="text-muted">¿Ya tienes cuenta?</span>
            <a href="{{ route('login') }}" class="auth-link ms-1">
                Inicia sesión aquí
            </a>
        </div>
    </form>
</x-guest-layout>
