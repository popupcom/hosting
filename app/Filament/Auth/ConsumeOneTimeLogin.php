<?php

namespace App\Filament\Auth;

use App\Models\OneTimeLoginToken;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\URL;

class ConsumeOneTimeLogin extends SimplePage
{
    public function mount(?string $token = null): void
    {
        if (Filament::auth()->check()) {
            $this->redirect(Filament::getUrl());

            return;
        }

        if (! URL::hasValidSignature(request())) {
            $this->redirectToLoginWithError('Der Anmeldelink ist ungültig oder abgelaufen.');

            return;
        }

        $plain = $token ?? (string) request()->query('token', '');

        if ($plain === '') {
            $this->redirectToLoginWithError('Der Anmeldelink ist ungültig oder abgelaufen.');

            return;
        }

        $hash = hash('sha256', $plain);

        $record = OneTimeLoginToken::query()
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if ($record === null) {
            $this->redirectToLoginWithError('Der Anmeldelink wurde bereits verwendet oder ist abgelaufen.');

            return;
        }

        $user = $record->user;

        if ($user instanceof FilamentUser && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
            $record->delete();
            $this->redirectToLoginWithError('Für dieses Konto ist der Zugang zum Admin-Bereich nicht freigeschaltet.');

            return;
        }

        $record->delete();

        Filament::auth()->login($user, remember: false);

        session()->regenerate();

        $this->redirect(Filament::getUrl());
    }

    protected function redirectToLoginWithError(string $message): void
    {
        session()->flash('one_time_login_error', $message);
        $this->redirect(filament()->getLoginUrl());
    }

    public function getTitle(): string|Htmlable
    {
        return 'Anmeldung';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Einmal-Anmeldung';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make('Du wirst angemeldet …'),
            ]);
    }
}
