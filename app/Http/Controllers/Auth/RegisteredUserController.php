<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'celular' => ['required', 'string', 'digits:8', 'unique:'.User::class],
            'password' => ['required', 'string', 'digits:4', 'confirmed'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe tener más de 255 caracteres.',
            'celular.required' => 'El celular es obligatorio.',
            'celular.digits' => 'El celular debe tener exactamente 8 dígitos.',
            'celular.unique' => 'El celular ya ha sido registrado.',
            'password.required' => 'El PIN es obligatorio.',
            'password.digits' => 'El PIN debe tener exactamente 4 dígitos.',
            'password.confirmed' => 'La confirmación del PIN no coincide.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'celular' => $request->celular,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirigir a la página de suscripción para crear su primer tenant
        return redirect()->route('suscripcion')->with('success', '¡Bienvenido! Crea tu primera tienda para comenzar.');
    }
}
