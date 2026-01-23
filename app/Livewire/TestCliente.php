<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Traits\RequiresTenant;
use Livewire\Component;
use Livewire\WithPagination;

class TestCliente extends Component
{
    use WithPagination, RequiresTenant;

    public $showModal = false;
    public $nombre = '';

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->nombre = '';
    }

    public function render()
    {
        return view('livewire.test-cliente', [
            'clientes' => Cliente::paginate(12),
        ]);
    }
}
