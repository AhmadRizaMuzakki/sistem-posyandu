<?php

namespace App\Livewire\Orangtua;

use Livewire\Component;
use Livewire\Attributes\Layout;

class OrangtuaDashboard extends Component
{
    #[Layout('layouts.orangtuadashboard')]
    public function render()
    {
        return view('livewire.orangtua.orang-tua');
    }
}
