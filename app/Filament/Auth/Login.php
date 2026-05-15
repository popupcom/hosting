<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $message = session()->pull('one_time_login_error');

        if (filled($message)) {
            Notification::make()
                ->title('Anmeldung nicht möglich')
                ->body($message)
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
