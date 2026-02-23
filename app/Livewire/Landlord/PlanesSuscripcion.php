<?php

namespace App\Livewire\Landlord;

use App\Models\PlanSuscripcion;
use App\Traits\SweetAlertTrait;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class PlanesSuscripcion extends Component
{
    use WithPagination, SweetAlertTrait;

    public $search = '';
    public $perPage = 10;

    // Modal
    public $modalOpen = false;
    public $planId = null;
    public $nombre;
    public $slug;
    public $duracion_meses;
    public $precio;
    public $descripcion;
    public $activo = true;
    public $orden = 0;

    // Características (array dinámico)
    public $caracteristicas = [];
    public $nuevaCaracteristica = '';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:planes_suscripcion,slug',
        'duracion_meses' => 'required|integer|min:0',
        'precio' => 'required|numeric|min:0',
        'descripcion' => 'nullable|string',
        'activo' => 'boolean',
        'orden' => 'integer|min:0',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['planId', 'nombre', 'slug', 'duracion_meses', 'precio', 'descripcion', 'activo', 'orden', 'caracteristicas']);
        $this->activo = true;
        $this->orden = PlanSuscripcion::max('orden') + 1;
        $this->modalOpen = true;
    }

    public function edit($id)
    {
        $plan = PlanSuscripcion::findOrFail($id);
        $this->planId = $plan->id;
        $this->nombre = $plan->nombre;
        $this->slug = $plan->slug;
        $this->duracion_meses = $plan->duracion_meses;
        $this->precio = $plan->precio;
        $this->descripcion = $plan->descripcion;
        $this->caracteristicas = $plan->caracteristicas ?? [];
        $this->activo = $plan->activo;
        $this->orden = $plan->orden;
        $this->modalOpen = true;
    }

    public function agregarCaracteristica()
    {
        if (!empty($this->nuevaCaracteristica)) {
            $this->caracteristicas[] = $this->nuevaCaracteristica;
            $this->nuevaCaracteristica = '';
        }
    }

    public function eliminarCaracteristica($index)
    {
        unset($this->caracteristicas[$index]);
        $this->caracteristicas = array_values($this->caracteristicas);
    }

    public function updatedNombre($value)
    {
        if (!$this->planId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        if ($this->planId) {
            $this->rules['slug'] = 'required|string|max:255|unique:planes_suscripcion,slug,' . $this->planId;
        }

        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'duracion_meses' => $this->duracion_meses,
            'precio' => $this->precio,
            'descripcion' => $this->descripcion,
            'caracteristicas' => $this->caracteristicas,
            'activo' => $this->activo,
            'orden' => $this->orden,
        ];

        if ($this->planId) {
            $plan = PlanSuscripcion::findOrFail($this->planId);
            $plan->update($data);
            $mensaje = 'Plan de suscripción actualizado correctamente';
        } else {
            PlanSuscripcion::create($data);
            $mensaje = 'Plan de suscripción creado correctamente';
        }

        $this->modalOpen = false;
        $this->reset(['planId', 'nombre', 'slug', 'duracion_meses', 'precio', 'descripcion', 'caracteristicas', 'activo', 'orden']);
        $this->alertSuccess($mensaje);
    }

    public function toggleActivo($id)
    {
        $plan = PlanSuscripcion::findOrFail($id);
        $plan->activo = !$plan->activo;
        $plan->save();

        $estado = $plan->activo ? 'activado' : 'desactivado';
        $this->alertSuccess("Plan {$estado} correctamente");
    }

    public function delete($id)
    {
        // Verificar si hay tenants usando este plan
        $plan = PlanSuscripcion::withCount('tenants')->findOrFail($id);

        if ($plan->tenants_count > 0) {
            $this->alertError("No se puede eliminar el plan porque hay {$plan->tenants_count} tenant(s) usando este plan");
            return;
        }

        $plan->delete();
        $this->alertSuccess('Plan eliminado correctamente');
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->resetErrorBag();
    }

    #[Layout('layouts.landlord.theme')]
    public function render()
    {
        $planes = PlanSuscripcion::query()
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->withCount('tenants')
            ->orderBy('orden')
            ->paginate($this->perPage);

        return view('livewire.landlord.planes-suscripcion', [
            'planes' => $planes
        ]);
    }
}
