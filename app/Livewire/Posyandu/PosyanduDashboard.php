<?php

namespace App\Livewire\Posyandu;

use Livewire\Component;
use Livewire\Attributes\Layout;

class PosyanduDashboard extends Component
{
    #[Layout('layouts.dashboard')]
    public function render()
    {
        return view('livewire.posyandu.admin-posyandu');
    }
}
