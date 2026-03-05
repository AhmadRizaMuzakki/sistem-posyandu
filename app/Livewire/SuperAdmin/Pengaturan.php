<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ActivityLog;
use App\Models\Posyandu;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Pengaturan extends Component
{
    #[Layout('layouts.superadmindashboard')]

    public $filterAction = '';
    public $filterSearch = '';
    public $filterPosyandu = '';

    public function render()
    {
        $query = ActivityLog::with(['user', 'posyandu'])
            ->orderByDesc('created_at');

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }
        if ($this->filterPosyandu !== '') {
            if ($this->filterPosyandu === 'null') {
                $query->whereNull('id_posyandu');
            } else {
                $query->where('id_posyandu', $this->filterPosyandu);
            }
        }
        if ($this->filterSearch) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->filterSearch . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $this->filterSearch . '%')->orWhere('email', 'like', '%' . $this->filterSearch . '%'));
            });
        }

        $logs = $query->paginate(20);

        $actionOptions = ActivityLog::distinct()->pluck('action')->sort()->values()->all();
        $posyanduOptions = Posyandu::orderBy('nama_posyandu')->get(['id_posyandu', 'nama_posyandu']);

        return view('livewire.super-admin.pengaturan', [
            'logs' => $logs,
            'actionOptions' => $actionOptions,
            'posyanduOptions' => $posyanduOptions,
        ]);
    }
}
