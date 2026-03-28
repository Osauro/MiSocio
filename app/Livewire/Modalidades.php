<?php

namespace App\Livewire;

use App\Models\ModalidadHabitacion;
use App\Models\TarifaHabitacion;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;

class Modalidades extends Component
{
    use RequiresTenant, SweetAlertTrait;

    // ─── Modal CRUD ─────────────────────────────────────────────────
    public $mostrarModal = false;
    public $editMode = false;
    public $modalidadId = null;
    public $nombre = '';
    public $horas = 1;
    public $activo = true;

    public function mount()
    {
        if (!hospedajesHabilitados()) {
            abort(403, 'El módulo de hospedajes no está habilitado.');
        }
    }

    public function getModalidadesProperty()
    {
        return ModalidadHabitacion::withCount('tarifas')
            ->orderBy('nombre')
            ->get();
    }

    public function create(): void
    {
        $this->resetFormulario();
        $this->mostrarModal = true;
    }

    public function edit(int $id): void
    {
        $mod = ModalidadHabitacion::findOrFail($id);
        $this->modalidadId = $mod->id;
        $this->editMode = true;
        $this->nombre = $mod->nombre;
        $this->horas = $mod->horas;
        $this->activo = $mod->activo;
        $this->mostrarModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:80',
            'horas'  => 'required|numeric|min:0.5|max:9999',
        ]);

        if ($this->editMode) {
            ModalidadHabitacion::findOrFail($this->modalidadId)->update([
                'nombre' => $this->nombre,
                'horas'  => $this->horas,
                'activo' => $this->activo,
            ]);
            $this->toast('success', 'Modalidad actualizada');
        } else {
            ModalidadHabitacion::create([
                'tenant_id' => $this->getTenantId(),
                'nombre'    => $this->nombre,
                'horas'     => $this->horas,
                'activo'    => $this->activo,
            ]);
            $this->toast('success', 'Modalidad creada');
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        $mod = ModalidadHabitacion::findOrFail($id);

        // Verificar si está siendo usada en tarifas
        $usada = TarifaHabitacion::where('modalidad', $mod->nombre)->exists();

        if ($usada) {
            $this->toast('error', 'No se puede eliminar: está siendo usada en tarifas');
            return;
        }

        $mod->delete();
        $this->toast('success', 'Modalidad eliminada');
    }

    public function toggleActivo(int $id): void
    {
        $mod = ModalidadHabitacion::findOrFail($id);
        $mod->update(['activo' => !$mod->activo]);
        $this->toast('success', $mod->activo ? 'Modalidad activada' : 'Modalidad desactivada');
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->modalidadId = null;
        $this->editMode = false;
        $this->nombre = '';
        $this->horas = 1;
        $this->activo = true;
    }

    public function render()
    {
        return view('livewire.modalidades', [
            'modalidades' => $this->modalidades,
        ]);
    }
}
