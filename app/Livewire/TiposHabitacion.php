<?php

namespace App\Livewire;

use App\Models\Habitacion;
use App\Models\ModalidadHabitacion;
use App\Models\TipoHabitacion;
use App\Models\TarifaHabitacion;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;

class TiposHabitacion extends Component
{
    use RequiresTenant, SweetAlertTrait;

    // ─── Modal Tipo de Habitación ───────────────────────────────────
    public $mostrarModal = false;
    public $editMode = false;
    public $tipoId = null;
    public $nombre = '';
    public $caracteristicas = ''; // String separado por comas
    public $capacidadMaxima = 2;
    public $color = '#308e87';

    // ─── Modal Habitación Individual ────────────────────────────────
    public $mostrarModalHabitacion = false;
    public $editModeHabitacion = false;
    public $habitacionId = null;
    public $habitacionTipoId = null;
    public $numero = '';
    public $piso = 1;
    public $estadoHab = 'disponible';

    // ─── Modal Tarifas ──────────────────────────────────────────────
    public $mostrarTarifas = false;
    public $tipoTarifaId = null;
    public $tarifas = [];
    public $tarifasPrecios = []; // ['Noche' => 50, 'Hora' => 30]
    public $tarifasPorPersona = []; // ['Noche' => true, 'Hora' => false]

    public function mount()
    {
        if (!hospedajesHabilitados()) {
            abort(403, 'El módulo de hospedajes no está habilitado.');
        }
    }

    public function getTiposProperty()
    {
        return TipoHabitacion::withCount('habitaciones')
            ->with(['tarifas', 'habitaciones' => fn($q) => $q->orderBy('piso')->orderBy('numero')])
            ->orderBy('nombre')
            ->get();
    }

    public function getModalidadesProperty()
    {
        return ModalidadHabitacion::orderBy('nombre')->get();
    }

    // ─── CRUD Tipos ────────────────────────────────────────────────
    public function create(): void
    {
        $this->tipoId = null;
        $this->editMode = false;
        $this->nombre = '';
        $this->caracteristicas = '';
        $this->capacidadMaxima = 2;
        $this->color = '#308e87';
        $this->mostrarModal = true;
    }

    public function edit(int $id): void
    {
        $tipo = TipoHabitacion::findOrFail($id);
        $this->tipoId = $tipo->id;
        $this->editMode = true;
        $this->nombre = $tipo->nombre;
        // Convertir array a string separado por comas
        $this->caracteristicas = is_array($tipo->caracteristicas)
            ? implode(', ', $tipo->caracteristicas)
            : '';
        $this->capacidadMaxima = $tipo->capacidad_maxima;
        $this->color = $tipo->color;
        $this->mostrarModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nombre'          => 'required|string|max:100',
            'capacidadMaxima' => 'required|integer|min:1|max:20',
            'color'           => 'required|string|max:20',
        ]);

        // Convertir string separado por comas a array
        $caracteristicasArray = !empty($this->caracteristicas)
            ? array_values(array_filter(array_map('trim', explode(',', $this->caracteristicas))))
            : null;

        if ($this->editMode) {
            TipoHabitacion::findOrFail($this->tipoId)->update([
                'nombre'           => $this->nombre,
                'caracteristicas'  => $caracteristicasArray,
                'capacidad_maxima' => $this->capacidadMaxima,
                'color'            => $this->color,
            ]);
            $this->toast('success', 'Tipo actualizado');
        } else {
            TipoHabitacion::create([
                'tenant_id'        => $this->getTenantId(),
                'nombre'           => $this->nombre,
                'caracteristicas'  => $caracteristicasArray,
                'capacidad_maxima' => $this->capacidadMaxima,
                'color'            => $this->color,
            ]);
            $this->toast('success', 'Tipo creado');
        }

        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->mostrarModal = false;
        $this->editMode = false;
        $this->tipoId = null;
    }

    public function eliminarTipo(int $id): void
    {
        $tipo = TipoHabitacion::withCount('habitaciones')->findOrFail($id);
        if ($tipo->habitaciones_count > 0) {
            $this->toast('error', 'No se puede eliminar: tiene habitaciones asignadas.');
            return;
        }
        $tipo->tarifas()->delete();
        $tipo->delete();
        $this->toast('success', 'Tipo eliminado');
    }

    // ─── CRUD Habitaciones ─────────────────────────────────────────
    public function crearHabitacion(int $tipoId): void
    {
        $this->habitacionId = null;
        $this->editModeHabitacion = false;
        $this->habitacionTipoId = $tipoId;
        $this->numero = '';
        $this->piso = 1;
        $this->estadoHab = 'disponible';
        $this->mostrarModalHabitacion = true;
    }

    public function editarHabitacion(int $id): void
    {
        $hab = Habitacion::findOrFail($id);
        $this->habitacionId = $hab->id;
        $this->editModeHabitacion = true;
        $this->habitacionTipoId = $hab->tipo_habitacion_id;
        $this->numero = $hab->numero;
        $this->piso = $hab->piso;
        $this->estadoHab = $hab->estado;
        $this->mostrarModalHabitacion = true;
    }

    public function guardarHabitacion(): void
    {
        $this->validate([
            'habitacionTipoId' => 'required|integer',
            'numero'           => 'required|string|max:20',
            'piso'             => 'required|integer|min:0|max:100',
            'estadoHab'        => 'required|in:disponible,ocupada,limpieza,mantenimiento',
        ]);

        if ($this->editModeHabitacion) {
            Habitacion::findOrFail($this->habitacionId)->update([
                'tipo_habitacion_id' => $this->habitacionTipoId,
                'numero'             => $this->numero,
                'piso'               => $this->piso,
                'estado'             => $this->estadoHab,
            ]);
            $this->toast('success', 'Habitación actualizada');
        } else {
            Habitacion::create([
                'tenant_id'          => $this->getTenantId(),
                'tipo_habitacion_id' => $this->habitacionTipoId,
                'numero'             => $this->numero,
                'piso'               => $this->piso,
                'estado'             => $this->estadoHab,
            ]);
            $this->toast('success', 'Habitación creada');
        }

        $this->cerrarModalHabitacion();
    }

    public function eliminarHabitacion(int $id): void
    {
        Habitacion::findOrFail($id)->delete();
        $this->toast('success', 'Habitación eliminada');
    }

    public function cerrarModalHabitacion(): void
    {
        $this->mostrarModalHabitacion = false;
        $this->editModeHabitacion = false;
        $this->habitacionId = null;
        $this->habitacionTipoId = null;
    }

    // ─── Tarifas ───────────────────────────────────────────────────
    public function verTarifas(int $tipoId): void
    {
        $this->tipoTarifaId = $tipoId;
        $this->tarifas      = TarifaHabitacion::where('tipo_habitacion_id', $tipoId)->get()->toArray();

        // Cargar precios actuales en arrays
        $this->tarifasPrecios = [];
        $this->tarifasPorPersona = [];

        $tarifasActuales = collect($this->tarifas)->keyBy('modalidad');
        foreach ($this->modalidades->where('activo', true) as $mod) {
            $t = $tarifasActuales[$mod->nombre] ?? null;
            $this->tarifasPrecios[$mod->nombre] = $t['precio'] ?? 0;
            $this->tarifasPorPersona[$mod->nombre] = $t['precio_por_persona'] ?? false;
        }

        $this->mostrarTarifas = true;
    }

    public function guardarTodasTarifas(): void
    {
        $this->validate([
            'tipoTarifaId' => 'required|integer',
        ]);

        $guardadas = 0;
        foreach ($this->tarifasPrecios as $modalidad => $precio) {
            if ($precio > 0) {
                TarifaHabitacion::updateOrCreate(
                    ['tipo_habitacion_id' => $this->tipoTarifaId, 'modalidad' => $modalidad],
                    [
                        'tenant_id'          => $this->getTenantId(),
                        'precio'             => $precio,
                        'precio_por_persona' => $this->tarifasPorPersona[$modalidad] ?? false,
                        'activo'             => true,
                    ]
                );
                $guardadas++;
            }
        }

        $this->tarifas = TarifaHabitacion::where('tipo_habitacion_id', $this->tipoTarifaId)->get()->toArray();
        $this->toast('success', "Se guardaron {$guardadas} tarifa(s)");
        $this->cerrarTarifas();
    }

    public function cerrarTarifas(): void
    {
        $this->mostrarTarifas = false;
        $this->tipoTarifaId  = null;
        $this->tarifas       = [];
        $this->tarifasPrecios = [];
        $this->tarifasPorPersona = [];
    }

    public function render()
    {
        return view('livewire.tipos-habitacion', [
            'tipos'       => $this->tipos,
            'modalidades' => $this->modalidades,
        ]);
    }
}
