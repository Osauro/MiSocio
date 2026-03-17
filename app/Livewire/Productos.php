<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\GaleriaImagen;
use App\Models\Medida;
use App\Models\Producto;
use App\Models\Tag;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Productos extends Component
{
    use WithPagination, SweetAlertTrait, RequiresTenant;

    public $search = '';
    public $perPage;
    public $editMode = false;
    public $addingNewCategoria = false;
    public $addingNewMedida = false;
    public $mostrarModal = false;

    // Galería / Imagen
    public $imagen;             // path relativo guardado en DB (string)
    public $imagen_preview_url; // URL completa solo para preview en el form
    public $galeria_id_seleccionado; // ID temporal para actualizar metadata de galería

    // Campos del producto
    public $productoId;
    public $categoria_id;
    public $nombre;
    public $codigo;
    public $producto_actual; // Para tener acceso al producto completo al editar
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

    protected $listeners = [
        'deleteProduct',
        'imagen-seleccionada' => 'imagenSeleccionada',
    ];

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
        $query = Producto::query();

        // Solo incluir productos eliminados cuando hay búsqueda
        if ($this->search) {
            $query->withTrashed();
        }

        return $query->with(['categoria', 'tags'])
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
        $this->mostrarModal = true;
    }

    /**
     * Mostrar el modal para editar un producto.
     */
    public function edit($id)
    {
        $this->resetForm();
        $producto = Producto::withTrashed()->findOrFail($id);

        $this->productoId = $producto->id;
        $this->categoria_id = $producto->categoria_id;
        $this->nombre = $producto->nombre;
        $this->codigo = $producto->codigo;
        $this->medida = $producto->medida;
        $this->cantidad = $producto->cantidad;
        $this->precio_de_compra = $producto->precio_de_compra;
        $this->precio_por_mayor = $producto->precio_por_mayor;
        $this->precio_por_menor = $producto->precio_por_menor;
        $this->stock = $producto->stock;
        $this->control = $producto->control;
        $this->tags_input = $producto->tags_string;
        $this->producto_actual = $producto; // Guardar el objeto completo
        // No asignamos $imagen para que en el form se muestre la imagen actual via $producto_actual
        $this->imagen = null;
        $this->imagen_preview_url = null;
        $this->galeria_id_seleccionado = null;

        $this->editMode = true;
        $this->mostrarModal = true;
        $this->dispatch('load-tags'); // Evento para cargar tags en Alpine
    }

    /**
     * Restaurar un producto eliminado.
     */
    public function restaurar($id)
    {
        $producto = Producto::withTrashed()->findOrFail($id);
        $producto->restore();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Producto restaurado exitosamente'
        ]);
    }

    /**
     * Guardar o actualizar un producto.
     */
    public function save()
    {
        // Si estamos agregando una categoría nueva, crearla/obtenerla ANTES de validar
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

        // Ahora validar con el ID correcto
        $this->validate();

        // Verificar nombre duplicado en el tenant
        $nombreDuplicado = Producto::where('tenant_id', currentTenantId())
            ->where('nombre', $this->nombre)
            ->when($this->editMode, fn($q) => $q->where('id', '!=', $this->productoId))
            ->exists();

        if ($nombreDuplicado) {
            $this->addError('nombre', 'Ya existe un producto con ese nombre en esta tienda.');
            return;
        }

        try {
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

            if ($this->editMode) {
                // Actualizar producto existente
                $producto = Producto::findOrFail($this->productoId);

                $dataToUpdate = [
                    'categoria_id'      => $this->categoria_id,
                    'nombre'            => $this->nombre,
                    'codigo'            => $this->codigo,
                    'medida'            => $this->medida,
                    'cantidad'          => $this->cantidad,
                    'precio_de_compra'  => $this->precio_de_compra,
                    'precio_por_mayor'  => $this->precio_por_mayor,
                    'precio_por_menor'  => $this->precio_por_menor,
                    'control'           => $this->control,
                ];

                // Solo actualizar imagen si el usuario seleccionó una nueva de la galería
                if ($this->galeria_id_seleccionado && $this->imagen) {
                    $dataToUpdate['imagen'] = $this->imagen;
                    $this->actualizarGaleria($this->galeria_id_seleccionado);
                }

                $producto->update($dataToUpdate);

                // Sincronizar tags del producto
                $producto->syncTagsFromString($this->tags_input);

                $this->toast('success', 'Producto actualizado exitosamente.');
            } else {
                // Crear nuevo producto
                $producto = Producto::create([
                    'tenant_id'         => currentTenantId(),
                    'categoria_id'      => $this->categoria_id,
                    'nombre'            => $this->nombre,
                    'codigo'            => $this->codigo,
                    'imagen'            => $this->imagen,
                    'medida'            => $this->medida,
                    'cantidad'          => $this->cantidad,
                    'precio_de_compra'  => $this->precio_de_compra,
                    'precio_por_mayor'  => $this->precio_por_mayor,
                    'precio_por_menor'  => $this->precio_por_menor,
                    'stock'             => 0,
                    'control'           => $this->control,
                ]);

                // Sincronizar tags del producto
                $producto->syncTagsFromString($this->tags_input);

                // Actualizar metadata de galería si se seleccionó una imagen
                if ($this->galeria_id_seleccionado) {
                    $this->actualizarGaleria($this->galeria_id_seleccionado);
                }

                $this->toast('success', 'Producto creado exitosamente.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->alertError('Error', 'Error al guardar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Abrir la galería para seleccionar imagen.
     */
    public function abrirGaleria(): void
    {
        if (empty(trim($this->nombre ?? ''))) {
            $this->addError('nombre', 'Escribe el nombre del producto antes de seleccionar una imagen.');
            return;
        }

        $this->dispatch('abrir-galeria', busqueda: trim($this->nombre));
    }

    /**
     * Recibir la imagen seleccionada desde GaleriaModal.
     */
    public function imagenSeleccionada(int $id, string $url, string $path): void
    {
        $this->galeria_id_seleccionado = $id;
        $this->imagen = $path;           // path relativo para guardar en DB
        $this->imagen_preview_url = $url; // URL completa para mostrar preview
    }

    /**
     * Actualizar nombre, tags y contador en la imagen de galería al guardar el producto.
     */
    private function actualizarGaleria(int $galeriaImagenId): void
    {
        $galeria = GaleriaImagen::find($galeriaImagenId);
        if (!$galeria) {
            return;
        }

        $galeria->increment('veces_usado');

        $nuevos = [trim($this->nombre)];
        if ($this->tags_input) {
            foreach (array_map('trim', explode(',', $this->tags_input)) as $t) {
                if ($t !== '') {
                    $nuevos[] = $t;
                }
            }
        }

        $galeria->mergeTags($nuevos);

        if (!$galeria->nombre) {
            $galeria->nombre = trim($this->nombre);
        }

        $galeria->save();
    }

    /**
     * Mostrar confirmación de eliminación.
     */
    public function confirmDeleteProduct($id)
    {
        $this->confirmDelete($id, '¿Eliminar producto?', 'Al eliminar el stock del producto se pondrá en 0.<br><br>Esta acción no se puede revertir.', 'deleteProduct');
    }

    /**
     * Eliminar un producto.
     */
    public function deleteProduct($id)
    {
        try {
            $producto = Producto::findOrFail($id);

            // Si el producto tiene stock, registrar salida en kardex
            if ($producto->stock > 0) {
                Kardex::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'producto_id' => $producto->id,
                    'entrada' => 0,
                    'salida' => $producto->stock,
                    'anterior' => $producto->stock,
                    'saldo' => 0,
                    'precio' => 0,
                    'total' => 0,
                    'obs' => "Producto eliminado - Stock puesto a 0"
                ]);

                // Actualizar stock a 0
                $producto->stock = 0;
                $producto->save();
            }

            // NO eliminar la imagen porque el producto puede ser restaurado
            // La imagen solo se eliminará cuando se elimine permanentemente o se actualice

            // Soft delete del producto
            $producto->delete();

            $this->toast('success', 'Producto eliminado exitosamente.<br>Stock puesto en 0.');
        } catch (\Exception $e) {
            $this->alertError('Error', 'No se pudo eliminar el producto: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar el modal.
     */
    public function closeModal()
    {
        $this->mostrarModal = false;
        $this->dispatch('reset-tags'); // Evento para limpiar tags en Alpine
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
        $this->imagen_preview_url = null;
        $this->galeria_id_seleccionado = null;
        $this->producto_actual = null;
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
