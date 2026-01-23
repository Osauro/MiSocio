<?php

namespace App\Livewire;

use App\Traits\RequiresTenant;
use Livewire\Component;

class HomeTenant extends Component
{
    use RequiresTenant;

    public function render()
    {
        return view('livewire.home-tenant');
    }
}
