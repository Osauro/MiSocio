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
    public $perPage;
    public $editMode = false;
    public $mostrarModal = false;

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
            'role' => 'required|in:tenant,user',
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

    protected $listeners = ['deleteUsuario', 'asociarUsuarioExistente'];

    public function mount()
    {
        $this->perPage = $_COOKIE['paginateUsuarios'] ?? 15;
    }

    public function render()
    {
        return view('livewire.usuarios', [
            'usuarios' => $this->getUsuarios(),
        ]);
    }

    public function getUsuarios()
    {
        return User::query()
            ->whereHas('tenants', function ($query) {
                $query->where('tenants.id', currentTenantId());
            })
            ->with(['tenants' => function ($query) {
                $query->where('tenants.id', currentTenantId());
            }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('celular', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->mostrarModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $usuario = User::with(['tenants' => function ($query) {
            $query->where('tenants.id', currentTenantId());
        }])->findOrFail($id);

        $this->usuarioId = $usuario->id;
        $this->name = $usuario->name;
        $this->celular = $usuario->celular;
        $this->role = $usuario->tenants->first()?->pivot?->role ?? 'user';
        $this->usuario_actual_imagen = $usuario->imagen;

        $this->editMode = true;
        $this->mostrarModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());

        // Verificar nombre duplicado en el tenant
        $nombreDuplicado = User::whereHas('tenants', function ($q) {
                $q->where('tenants.id', currentTenantId());
            })
            ->where('name', $this->name)
            ->when($this->editMode, fn($q) => $q->where('id', '!=', $this->usuarioId))
            ->exists();

        if ($nombreDuplicado) {
            $this->addError('name', 'Ya existe un usuario con ese nombre en esta tienda.');
            return;
        }

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

                // Actualizar el rol en la tabla pivot
                $usuario->tenants()->updateExistingPivot(currentTenantId(), [
                    'role' => $this->role,
                ]);

                $this->toast('success', 'Usuario actualizado exitosamente.');
            } else {
                // Verificar si ya existe un usuario con ese celular
                $usuarioExistente = User::where('celular', $this->celular)->first();

                if ($usuarioExistente) {
                    // Verificar si ya está asociado a este tenant
                    if ($usuarioExistente->tenants()->where('tenants.id', currentTenantId())->exists()) {
                        $this->alertWarning('Atención', 'Este usuario ya está asociado a tu tienda.');
                        return;
                    }

                    // Mostrar confirmación para asociar usuario existente
                    $this->dispatch('swal:confirm', [
                        'id' => $usuarioExistente->id,
                        'title' => '¡Usuario existente!',
                        'text' => "El usuario <strong>{$usuarioExistente->name}</strong> ya tiene una contraseña asignada. ¿Deseas asociarlo a tu tienda?",
                        'event' => 'asociarUsuarioExistente',
                        'confirmButtonText' => 'Sí, asociar',
                        'confirmButtonColor' => '#198754',
                    ]);
                    return;
                }

                $user = User::create([
                    'name' => $this->name,
                    'celular' => $this->celular,
                    'password' => Hash::make($this->password),
                    'imagen' => $imagenPath,
                ]);

                // Asociar el usuario con el tenant actual
                $user->tenants()->attach(currentTenantId(), [
                    'role' => $this->role,
                    'is_active' => true,
                ]);

                $this->toast('success', 'Usuario creado exitosamente.');
            }

            $this->resetPage();
            $this->closeModal();
        } catch (\Exception $e) {
            $this->alertError('Error', 'Error al guardar el usuario: ' . $e->getMessage());
        }
    }

    public function asociarUsuarioExistente($id)
    {
        try {
            $usuario = User::findOrFail($id);

            $usuario->tenants()->attach(currentTenantId(), [
                'role' => $this->role ?: 'user',
                'is_active' => true,
            ]);

            $this->toast('success', "Usuario {$usuario->name} asociado exitosamente.");
            $this->resetPage();
            $this->closeModal();
        } catch (\Exception $e) {
            $this->alertError('Error', 'No se pudo asociar el usuario: ' . $e->getMessage());
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
        $this->mostrarModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->usuarioId = null;
        $this->name = '';
        $this->celular = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'user';
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
