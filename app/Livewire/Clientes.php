<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Clientes extends Component
{
    use WithPagination, SweetAlertTrait, RequiresTenant;

    public $search = '';
    public $editMode = false;
    public $clienteId;
    public $nombre = '';
    public $celular = '';
    public $nit = '';
    public $correo = '';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'celular' => 'required|string|max:20',
        'nit' => 'nullable|string|max:50',
        'correo' => 'nullable|email|max:255',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio',
        'celular.required' => 'El celular es obligatorio',
        'correo.email' => 'El correo no es válido',
    ];

    protected $listeners = ['deleteCliente'];

    public function render()
    {
        return view('livewire.clientes', [
            'clientes' => $this->getClientes(),
        ]);
    }

    public function getClientes()
    {
        $query = Cliente::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('celular', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('nombre')->paginate(21);
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
        $cliente = Cliente::findOrFail($id);

        $this->clienteId = $cliente->id;
        $this->nombre = $cliente->nombre;
        $this->celular = $cliente->celular;
        $this->nit = $cliente->nit;
        $this->correo = $cliente->correo;

        $this->editMode = true;
        $this->dispatch('showmodal');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'celular' => $this->celular,
            'nit' => $this->nit,
            'correo' => $this->correo,
        ];

        if ($this->editMode) {
            $cliente = Cliente::findOrFail($this->clienteId);
            $cliente->update($data);
            $this->toast('success', 'Cliente actualizado exitosamente.');
        } else {
            $data['tenant_id'] = Auth::user()->tenant_id;
            Cliente::create($data);
            $this->toast('success', 'Cliente creado exitosamente.');
        }

        $this->dispatch('closemodal');
        $this->resetForm();
    }

    public function confirmDeleteCliente($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar cliente?',
            'text' => 'Esta acción no se puede deshacer',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'method' => 'deleteCliente',
            'params' => $id,
        ]);
    }

    public function deleteCliente($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        $this->toast('success', 'Cliente eliminado exitosamente.');
        $this->dispatch('focus-search');
    }

    public function closeModal()
    {
        $this->dispatch('closemodal');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->clienteId = null;
        $this->nombre = '';
        $this->celular = '';
        $this->nit = '';
        $this->correo = '';
        $this->resetErrorBag();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
