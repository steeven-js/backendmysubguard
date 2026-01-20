<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;

class Login extends BaseLogin
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        // Pre-fill credentials in local environment
        if (app()->environment('local')) {
            $this->form->fill([
                'email' => 'admin@mysubguard.com',
                'password' => 'password123',
            ]);
        } else {
            $this->form->fill();
        }
    }
}
