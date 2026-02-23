<x-guest-layout>
    <h1 class="auth-title">Entrar</h1>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                autofocus
                autocomplete="tel"
                maxlength="8"
                placeholder="Ingresa tu número de celular"
            />
            @error('celular')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password (PIN) -->
        <div class="mb-4">
            <label for="password" class="form-label">Contraseña</label>
            <input
                id="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                required
                autocomplete="current-password"
                maxlength="4"
                inputmode="numeric"
                placeholder="PIN de 4 dígitos"
            />
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-5">
            <div class="form-check">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="form-check-input"
                    name="remember"
                >
                <label class="form-check-label" for="remember_me">
                    Recuérdame
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mb-4">
            <button type="submit" class="btn-primary-custom">
                Entrar
            </button>
        </div>

        <!-- Register Link -->
        <div class="text-center mt-5">
            <span class="text-muted">¿No tienes cuenta?</span>
            <a href="{{ route('register') }}" class="auth-link ms-1">
                Regístrate aquí
            </a>
        </div>
    </form>
</x-guest-layout>
