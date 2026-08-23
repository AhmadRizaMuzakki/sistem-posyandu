<?php

namespace App\Livewire\Orangtua\Traits;

use Illuminate\Support\Facades\Auth;

trait ResolvesNoKk
{
    protected function resolveNoKk(): ?string
    {
        $user = Auth::user();
        if ($user->email && str_ends_with($user->email, '@gmail.com')) {
            return str_replace('@gmail.com', '', $user->email);
        }

        return null;
    }
}
