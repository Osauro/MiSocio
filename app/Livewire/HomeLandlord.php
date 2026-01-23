<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class HomeLandlord extends Component
{
    #[Layout('layouts.landlord.theme')]
    public function render()
    {
        return view('livewire.home-landlord');
    }
}
