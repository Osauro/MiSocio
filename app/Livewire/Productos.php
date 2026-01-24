<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Medida;
use App\Models\Producto;
use App\Models\Tag;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Productos extends Component
{
    use WithPagination, WithFileUploads, SweetAlertTrait, RequiresTenant;

    public $search = '';
    public $perPage;
    public $editMode = false;
    public $addingNewCategoria = false;
    public $addingNewMedida = false;

    // Campos del producto
    public $productoId;
    public $categoria_id;
    public $nombre;
    public $codigo;
    public $imagen;
    public $producto_actual_imagen; // Para mostrar la imagen existente al editar
    public $medida;
    public $cantidad;
    public $precio_de_compra;
    public $precio_por_mayor;
    public $precio_por_menor;
    public $stock;
    public $control = true;
    public $tags_input = '';

    protected $rules = [
        'categoria_id' => 'required|exists:categorias,id',
        'nombre' => 'required|string|max:255',
        'codigo' => 'nullable|string|max:255',
        'imagen' => 'nullable|image',
        'medida' => 'required|string|max:10',
        'cantidad' => 'required|integer|min:1',
        'precio_de_compra' => 'required|numeric|min:0',
        'precio_por_mayor' => 'required|numeric|min:0',
        'precio_por_menor' => 'required|numeric|min:0',
        'control' => 'boolean',
        'tags_input' => 'nullable|string',
    ];

    protected $messages = [
        'categoria_id.required' => 'La categoría es obligatoria',
        'categoria_id.exists' => 'La categoría seleccionada no es válida',
        'nombre.required' => 'El nombre es obligatorio',
        'medida.required' => 'La medida es obligatoria',
        'cantidad.required' => 'La cantidad es obligatoria',
        'cantidad.min' => 'La cantidad debe ser mayor a 0',
        'precio_de_compra.required' => 'El precio de compra es obligatorio',
        'precio_de_compra.min' => 'El precio de compra debe ser mayor o igual a 0',
        'precio_por_mayor.required' => 'El precio por mayor es obligatorio',
        'precio_por_mayor.min' => 'El precio por mayor debe ser mayor o igual a 0',
        'precio_por_menor.required' => 'El precio por menor es obligatorio',
        'precio_por_menor.min' => 'El precio por menor debe ser mayor o igual a 0',
    ];

    protected $listeners = ['deleteProduct'];

    public function mount()
    {
        $this->perPage = $_COOKIE['paginateProductos'] ?? 15;
    }

    public function render()
    {
        return view('livewire.productos', [
            'productos' => $this->getProductos(),
            'categorias' => $this->getCategorias(),
            'medidas' => $this->getMedidas()
        ]);
    }

    /**
     * Obtener los productos filtrados.
     */
    public function getProductos()
    {
        return Producto::with(['categoria', 'tags'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                        ->orWhere('codigo', 'like', '%' . $this->search . '%')
                        ->orWhereHas('categoria', function ($query) {
                            $query->where('nombre', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('tags', function ($query) {
                            $query->where('nombre', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('nombre')
            ->paginate($this->perPage);
    }

    /**
     * Obtener las categorías del tenant actual.
     */
    public function getCategorias()
    {
        return Categoria::orderBy('nombre')->get();
    }

    /**
     * Obtener las medidas del tenant actual.
     */
    public function getMedidas()
    {
        return Medida::orderBy('nombre')->get();
    }

    /**
     * Alternar entre select de categoría e input para nueva categoría.
     */
    public function toggleCategoriaInput()
    {
        $this->addingNewCategoria = !$this->addingNewCategoria;
        if ($this->addingNewCategoria) {
            $this->categoria_id = '';
        }
    }

    /**
     * Alternar entre select de medida e input para nueva medida.
     */
    public function toggleMedidaInput()
    {
        $this->addingNewMedida = !$this->addingNewMedida;
        if ($this->addingNewMedida) {
            $this->medida = '';
        }
    }

    /**
     * Mostrar el modal para crear un nuevo producto.
     */
    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->dispatch('showmodal');
    }

    /**
     * Mostrar el modal para editar un producto.
     */
    public function edit($id)
    {
        $this->resetForm();
        $producto = Producto::findOrFail($id);

        $this->productoId = $producto->id;
        $this->categoria_id = $producto->categoria_id;
        $this->nombre = $producto->nombre;
        $this->codigo = $producto->codigo;
        // NO asignar la imagen existente al campo, solo guardarla para preview
        $this->imagen = null;
        $this->medida = $producto->medida;
        $this->cantidad = $producto->cantidad;
        $this->precio_de_compra = $producto->precio_de_compra;
        $this->precio_por_mayor = $producto->precio_por_mayor;
        $this->precio_por_menor = $producto->precio_por_menor;
        $this->stock = $producto->stock;
        $this->control = $producto->control;
        $this->tags_input = $producto->tags_string;
        $this->producto_actual_imagen = $producto->imagen; // Guardar la imagen actual

        $this->editMode = true;
        $this->dispatch('showmodal');
    }

    /**
     * Guardar o actualizar un producto.
     */
    public function save()
    {
        $this->validate();

        try {
            // Si estamos agregando una categoría nueva, guardarla primero
            if ($this->addingNewCategoria && $this->categoria_id) {
                $categoriaExistente = Categoria::where('nombre', trim($this->categoria_id))->first();

                if (!$categoriaExistente) {
                    $nuevaCategoria = Categoria::create([
                        'tenant_id' => currentTenantId(),
                        'nombre' => trim($this->categoria_id),
                    ]);
                    $this->categoria_id = $nuevaCategoria->id;
                } else {
                    $this->categoria_id = $categoriaExistente->id;
                }
            }

            // Si estamos agregando una medida nueva, guardarla primero
            if ($this->addingNewMedida && $this->medida) {
                $medidaExistente = Medida::where('nombre', trim($this->medida))->first();

                if (!$medidaExistente) {
                    Medida::create([
                        'tenant_id' => currentTenantId(),
                        'nombre' => trim($this->medida),
                    ]);
                }
            }

            // Procesar imagen si se subió una nueva
            $imagenPath = null;
            if ($this->imagen) {
                $imagenPath = $this->processImage($this->imagen);
            }

            if ($this->editMode) {
                // Actualizar producto existente
                $producto = Producto::findOrFail($this->productoId);

                $dataToUpdate = [
                    'categoria_id' => $this->categoria_id,
                    'nombre' => $this->nombre,
                    'codigo' => $this->codigo,
                    'medida' => $this->medida,
                    'cantidad' => $this->cantidad,
                    'precio_de_compra' => $this->precio_de_compra,
                    'precio_por_mayor' => $this->precio_por_mayor,
                    'precio_por_menor' => $this->precio_por_menor,
                    'control' => $this->control,
                ];

                // Solo actualizar imagen si se subió una nueva
                if ($imagenPath) {
                    // Eliminar imagen anterior si existe
                    if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                        Storage::disk('public')->delete($producto->imagen);
                    }
                    $dataToUpdate['imagen'] = $imagenPath;
                }

                $producto->update($dataToUpdate);

                // Sincronizar tags
                $producto->syncTagsFromString($this->tags_input);

                $this->toast('success', 'Producto actualizado exitosamente.');
            } else {
                // Crear nuevo producto
                $producto = Producto::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'categoria_id' => $this->categoria_id,
                    'nombre' => $this->nombre,
                    'codigo' => $this->codigo,
                    'imagen' => $imagenPath,
                    'medida' => $this->medida,
                    'cantidad' => $this->cantidad,
                    'precio_de_compra' => $this->precio_de_compra,
                    'precio_por_mayor' => $this->precio_por_mayor,
                    'precio_por_menor' => $this->precio_por_menor,
                    'stock' => 0,
                    'control' => $this->control,
                ]);

                // Sincronizar tags
                $producto->syncTagsFromString($this->tags_input);

                $this->toast('success', 'Producto creado exitosamente.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->alertError('Error', 'Error al guardar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Procesar y guardar imagen redimensionada.
     */
    private function processImage($imagen)
    {
        // Generar nombre único para la imagen
        $filename = time() . '_' . uniqid() . '.jpg';
        $path = 'productos/' . $filename;

        // Procesar imagen con Intervention Image
        $imageProcessed = Image::read($imagen->getRealPath())
            ->cover(512, 512)
            ->toJpeg(90);

        // Guardar en storage/app/public/productos
        Storage::disk('public')->put($path, (string) $imageProcessed);

        return $path;
    }

    /**
     * Mostrar confirmación de eliminación.
     */
    public function confirmDeleteProduct($id)
    {
        $this->confirmDelete($id, '¿Eliminar producto?', 'Esta acción no se puede revertir', 'deleteProduct');
    }

    /**
     * Eliminar un producto.
     */
    public function deleteProduct($id)
    {
        try {
            $producto = Producto::findOrFail($id);

            // Eliminar imagen si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }

            $producto->delete();

            $this->toast('success', 'Producto eliminado exitosamente.');
            $this->resetPage();
        } catch (\Exception $e) {
            $this->alertError('Error', 'No se pudo eliminar el producto: ' . $e->getMessage());
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
        $this->productoId = null;
        $this->categoria_id = null;
        $this->nombre = '';
        $this->codigo = '';
        $this->imagen = null;
        $this->producto_actual_imagen = null;
        $this->medida = '';
        $this->cantidad = null;
        $this->precio_de_compra = null;
        $this->precio_por_mayor = null;
        $this->precio_por_menor = null;
        $this->stock = null;
        $this->control = true;
        $this->tags_input = '';
        $this->addingNewCategoria = false;
        $this->addingNewMedida = false;
        $this->resetErrorBag();
    }
}
