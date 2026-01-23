<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class PerfilUsuario extends Component
{
    use WithFileUploads;

    public $mostrar = false;
    public $editando = false;
    public $nombre;
    public $celular;
    public $password;
    public $password_confirmation;
    public $imagen;

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'celular' => 'required|string|max:15|unique:users,celular,' . Auth::id(),
        ];

        if ($this->password) {
            $rules['password'] = 'required|digits:4|confirmed';
        }

        return $rules;
    }

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $user = Auth::user();
        $this->nombre = $user->name;
        $this->celular = $user->celular;
    }

    public function updatedImagen()
    {
        // Validar que sea una imagen
        $this->validate([
            'imagen' => 'image',
        ]);

        $user = Auth::user();

        // Eliminar imagen anterior si existe
        if ($user->imagen && Storage::disk('public')->exists($user->imagen)) {
            Storage::disk('public')->delete($user->imagen);
        }

        // Procesar imagen con Intervention Image
        $image = Image::read($this->imagen->getRealPath());

        // Redimensionar a 512x512
        $image->cover(512, 512);

        // Generar nombre único
        $filename = 'usuario_' . $user->id . '_' . time() . '.jpg';
        $path = 'usuarios/' . $filename;

        // Guardar imagen procesada
        Storage::disk('public')->put(
            $path,
            $image->toJpeg(90)->toString()
        );

        // Actualizar ruta en base de datos
        $user->imagen = $path;
        $user->save();

        // Limpiar imagen temporal
        $this->imagen = null;

        // Recargar usuario para obtener photo_url actualizado
        $user->refresh();

        // Disparar evento para actualizar avatar en header
        $this->dispatch('avatarUpdated', ['url' => $user->photo_url]);

        $this->dispatch('swal:success',
            title: '¡Éxito!',
            text: 'Imagen actualizada correctamente'
        );
    }

    public function toggleSidebar()
    {
        $this->mostrar = !$this->mostrar;
        if (!$this->mostrar) {
            $this->editando = false;
            $this->cargarDatos();
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    public function toggleEditar()
    {
        $this->editando = !$this->editando;
        if (!$this->editando) {
            $this->cargarDatos();
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    public function guardar()
    {
        $this->validate();

        $user = Auth::user();
        $user->name = $this->nombre;
        $user->celular = $this->celular;

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        $this->editando = false;
        $this->password = '';
        $this->password_confirmation = '';

        $this->dispatch('swal:success',
            title: '¡Éxito!',
            text: 'Perfil actualizado correctamente'
        );
    }

    public function cancelar()
    {
        $this->editando = false;
        $this->cargarDatos();
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function render()
    {
        return view('livewire.perfil-usuario');
    }
}
