<?php

namespace App\Livewire;

use App\Models\Kardex;
use App\Models\Producto;
use App\Traits\RequiresTenant;
use Livewire\Component;

class KardexModal extends Component
{
    use RequiresTenant;

    public $mostrar = false;
    public $productoId = null;
    public $producto = null;
    public $movimientos = [];
    public $perPage = 10;

    protected $listeners = ['mostrarKardex', 'cerrarKardex'];

    /**
     * Inicializar el componente con el valor de perPage desde cookies/localStorage.
     */
    public function mount()
    {
        // Intentar obtener el valor desde la cookie
        if (request()->hasCookie('paginateKardexModal')) {
            $this->perPage = (int) request()->cookie('paginateKardexModal');
        }
    }

    /**
     * Actualizar los movimientos cuando cambia perPage.
     */
    public function updatedPerPage()
    {
        if ($this->productoId) {
            $this->cargarMovimientos();
        }
    }

    /**
     * Cargar los movimientos del kardex.
     */
    private function cargarMovimientos()
    {
        $this->movimientos = Kardex::where('producto_id', $this->productoId)
            ->with('user:id,name')
            ->latest()
            ->limit($this->perPage)
            ->get();
    }

    /**
     * Mostrar los últimos movimientos del kardex de un producto.
     */
    public function mostrarKardex($productoId, $perPage = null)
    {
        $this->productoId = $productoId;

        if ($perPage !== null) {
            $this->perPage = $perPage;
        }

        // Obtener el producto (incluso si está eliminado)
        $this->producto = Producto::withTrashed()->findOrFail($productoId);

        // Cargar los movimientos
        $this->cargarMovimientos();

        $this->mostrar = true;
    }

    /**
     * Cerrar el modal de kardex.
     */
    public function cerrarKardex()
    {
        $this->mostrar = false;
        $this->productoId = null;
        $this->producto = null;
        $this->movimientos = [];
    }

    public function render()
    {
        return view('livewire.kardex-modal');
    }
}
