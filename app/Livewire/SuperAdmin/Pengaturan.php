<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Pengaturan extends Component
{
    #[Layout('layouts.superadmindashboard')]

    public $filterAction = '';
    public $filterSearch = '';

    public function render()
    {
        $query = ActivityLog::with('user')
            ->orderByDesc('created_at');

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }
        if ($this->filterSearch) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->filterSearch . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $this->filterSearch . '%')->orWhere('email', 'like', '%' . $this->filterSearch . '%'));
            });
        }

        $logs = $query->paginate(20);

        $actionOptions = ActivityLog::distinct()->pluck('action')->sort()->values()->all();

        return view('livewire.super-admin.pengaturan', [
            'logs' => $logs,
            'actionOptions' => $actionOptions,
        ]);
    }
}
