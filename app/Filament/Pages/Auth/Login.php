<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class Login extends BaseLogin
{
    public function mount(): void
    {
        // Redirect to the main login page
        redirect('/login');
    }
}

