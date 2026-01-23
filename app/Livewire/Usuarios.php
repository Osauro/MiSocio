<?php

namespace App\Livewire;

use App\Models\User;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Usuarios extends Component
{
    use WithPagination, WithFileUploads, SweetAlertTrait, RequiresTenant;

    public $search = '';
    public $editMode = false;

    // Campos del usuario
    public $usuarioId;
    public $name;
    public $celular;
    public $password;
    public $password_confirmation;
    public $role;
    public $imagen;
    public $usuario_actual_imagen;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'celular' => 'required|string|digits:8',
            'password' => $this->editMode ? 'nullable|string|digits:4|confirmed' : 'required|string|digits:4|confirmed',
            'role' => 'required|in:landlord,tenant',
            'imagen' => 'nullable|image|max:2048',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre es obligatorio',
        'celular.required' => 'El celular es obligatorio',
        'celular.digits' => 'El celular debe tener 8 dígitos',
        'password.digits' => 'El PIN debe tener 4 dígitos',
        'password.confirmed' => 'Los PINs no coinciden',
        'role.required' => 'El rol es obligatorio',
    ];

    protected $listeners = ['deleteUsuario'];

    public function render()
    {
        return view('livewire.usuarios', [
            'usuarios' => $this->getUsuarios(),
        ]);
    }

    public function getUsuarios()
    {
        return User::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('celular', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(15);
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->dispatch('showmodal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $usuario = User::findOrFail($id);

        $this->usuarioId = $usuario->id;
        $this->name = $usuario->name;
        $this->celular = $usuario->celular;
        $this->role = $usuario->role;
        $this->usuario_actual_imagen = $usuario->imagen;

        $this->editMode = true;
        $this->dispatch('showmodal');
    }

    public function save()
    {
        $this->validate($this->rules());

        try {
            // Procesar imagen si se subió una nueva
            $imagenPath = null;
            if ($this->imagen) {
                $imagenPath = $this->processImage($this->imagen);
            }

            if ($this->editMode) {
                $usuario = User::findOrFail($this->usuarioId);
                $data = [
                    'name' => $this->name,
                    'celular' => $this->celular,
                    'role' => $this->role,
                ];

                if ($this->password) {
                    $data['password'] = Hash::make($this->password);
                }

                // Solo actualizar imagen si se subió una nueva
                if ($imagenPath) {
                    // Eliminar imagen anterior si existe
                    if ($usuario->imagen && Storage::disk('public')->exists($usuario->imagen)) {
                        Storage::disk('public')->delete($usuario->imagen);
                    }
                    $data['imagen'] = $imagenPath;
                }

                $usuario->update($data);

                $this->toast('success', 'Usuario actualizado exitosamente.');
            } else {
                User::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'name' => $this->name,
                    'celular' => $this->celular,
                    'password' => Hash::make($this->password),
                    'role' => $this->role,
                    'imagen' => $imagenPath,
                ]);

                $this->toast('success', 'Usuario creado exitosamente.');
            }

            $this->resetPage();
            $this->closeModal();
        } catch (\Exception $e) {
            $this->alertError('Error', 'Error al guardar el usuario: ' . $e->getMessage());
        }
    }

    public function confirmDeleteUsuario($id)
    {
        $this->confirmDelete($id, '¿Eliminar usuario?', 'Esta acción no se puede revertir', 'deleteUsuario');
    }

    public function deleteUsuario($id)
    {
        try {
            $usuario = User::findOrFail($id);
            $usuario->delete();

            $this->toast('success', 'Usuario eliminado exitosamente.');
            $this->resetPage();
        } catch (\Exception $e) {
            $this->alertError('Error', 'No se pudo eliminar el usuario: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->dispatch('closemodal');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->usuarioId = null;
        $this->name = '';
        $this->celular = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'tenant';
        $this->imagen = null;
        $this->usuario_actual_imagen = null;
        $this->resetErrorBag();
    }

    private function processImage($imagen)
    {
        // Generar nombre único para la imagen
        $filename = time() . '_' . uniqid() . '.jpg';
        $path = 'usuarios/' . $filename;

        // Procesar imagen con Intervention Image (512x512, JPG optimizado al 90%)
        $imageProcessed = Image::read($imagen->getRealPath())
            ->cover(512, 512)
            ->toJpeg(90);

        // Guardar en storage/app/public
        Storage::disk('public')->put($path, (string) $imageProcessed);

        return $path;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
