<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Categorias extends Component
{
    use WithPagination, WithFileUploads, SweetAlertTrait, RequiresTenant;

    public $search = '';
    public $perPage;
    public $editMode = false;

    // Campos de la categoría
    public $categoriaId;
    public $nombre;
    public $imagen;
    public $categoria_actual_imagen; // Para mostrar la imagen existente al editar

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'imagen' => 'nullable|image',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio',
        'nombre.max' => 'El nombre no puede exceder 255 caracteres',
        'imagen.image' => 'El archivo debe ser una imagen',
    ];

    protected $listeners = ['deleteCategoria'];

    public function mount()
    {
        $this->perPage = $_COOKIE['paginateCategorias'] ?? 15;
    }

    public function render()
    {
        return view('livewire.categorias', [
            'categorias' => $this->getCategorias(),
        ]);
    }

    /**
     * Obtener las categorías filtradas.
     */
    public function getCategorias()
    {
        $query = Categoria::withCount('productos');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('nombre')->paginate($this->perPage);
    }

    /**
     * Mostrar el modal para crear una nueva categoría.
     */
    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->dispatch('showmodal');
    }

    /**
     * Mostrar el modal para editar una categoría.
     */
    public function edit($id)
    {
        $this->resetForm();
        $categoria = Categoria::findOrFail($id);

        $this->categoriaId = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->categoria_actual_imagen = $categoria->imagen;

        $this->editMode = true;
        $this->dispatch('showmodal');
    }

    /**
     * Guardar o actualizar una categoría.
     */
    public function save()
    {
        $this->validate();

        try {
            // Procesar imagen si se subió una nueva
            $imagenPath = null;
            if ($this->imagen) {
                $imagenPath = $this->processImage($this->imagen);
            }

            if ($this->editMode) {
                // Actualizar categoría existente
                $categoria = Categoria::findOrFail($this->categoriaId);

                $dataToUpdate = [
                    'nombre' => $this->nombre,
                ];

                // Solo actualizar imagen si se subió una nueva
                if ($imagenPath) {
                    // Eliminar imagen anterior si existe
                    if ($categoria->imagen && Storage::disk('public')->exists($categoria->imagen)) {
                        Storage::disk('public')->delete($categoria->imagen);
                    }
                    $dataToUpdate['imagen'] = $imagenPath;
                }

                $categoria->update($dataToUpdate);

                $this->toast('success', 'Categoría actualizada exitosamente.');
            } else {
                // Crear nueva categoría
                Categoria::create([
                    'tenant_id' => currentTenantId(),
                    'nombre' => $this->nombre,
                    'imagen' => $imagenPath,
                ]);

                $this->toast('success', 'Categoría creada exitosamente.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->alertError('Error', 'Error al guardar la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Procesar y guardar imagen redimensionada.
     */
    private function processImage($imagen)
    {
        // Generar nombre único para la imagen
        $filename = time() . '_' . uniqid() . '.jpg';
        $path = 'categorias/' . $filename;

        // Procesar imagen con Intervention Image
        $imageProcessed = Image::read($imagen->getRealPath())
            ->cover(512, 512)
            ->toJpeg(90);

        // Guardar en storage/app/public/categorias
        Storage::disk('public')->put($path, (string) $imageProcessed);

        return $path;
    }

    /**
     * Mostrar confirmación de eliminación.
     */
    public function confirmDeleteCategoria($id)
    {
        $this->confirmDelete($id, '¿Eliminar categoría?', 'Esta acción no se puede revertir', 'deleteCategoria');
    }

    /**
     * Eliminar una categoría.
     */
    public function deleteCategoria($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            // Eliminar imagen si existe
            if ($categoria->imagen && Storage::disk('public')->exists($categoria->imagen)) {
                Storage::disk('public')->delete($categoria->imagen);
            }

            $categoria->delete();

            $this->toast('success', 'Categoría eliminada exitosamente.');
            $this->resetPage();
        } catch (\Exception $e) {
            $this->alertError('Error', 'No se pudo eliminar la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar el modal.
     */
    public function closeModal()
    {
        $this->dispatch('closemodal');
        $this->resetForm();
    }

    /**
     * Resetear el formulario.
     */
    private function resetForm()
    {
        $this->categoriaId = null;
        $this->nombre = '';
        $this->resetErrorBag();
    }

    /**
     * Actualizar resultados de búsqueda.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
}
