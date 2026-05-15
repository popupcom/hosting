<?php

namespace App\Filament\Auth;

use App\Mail\OneTimeLoginMail;
use App\Models\User;
use App\Services\OneTimeLoginLinkService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsIconAlias;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;

/**
 * @property-read Action $loginAction
 * @property-read Schema $form
 */
class RequestOneTimeLogin extends SimplePage
{
    use WithRateLimiting;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }

    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data = $this->form->getState();
        $email = $data['email'] ?? '';

        $user = User::query()->where('email', $email)->first();

        if ($user instanceof FilamentUser && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
            $user = null;
        }

        if ($user !== null) {
            $url = app(OneTimeLoginLinkService::class)->createSignedUrlForUser($user);
            Mail::to($user->email)->send(new OneTimeLoginMail($url));
        }

        Notification::make()
            ->title('E-Mail unterwegs')
            ->body('Falls ein Konto mit dieser Adresse existiert, haben wir dir einen Einmal-Anmeldelink geschickt.')
            ->success()
            ->send();

        $this->form->fill();
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title('Zu viele Versuche')
            ->body('Bitte warte '.$exception->secondsUntilAvailable.' Sekunden und versuche es erneut.')
            ->danger();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('E-Mail-Adresse')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Zurück zur Anmeldung')
            ->icon(match (__('filament-panels::layout.direction')) {
                'rtl' => FilamentIcon::resolve(PanelsIconAlias::PAGES_PASSWORD_RESET_REQUEST_PASSWORD_RESET_ACTIONS_LOGIN_RTL) ?? Heroicon::ArrowRight,
                default => FilamentIcon::resolve(PanelsIconAlias::PAGES_PASSWORD_RESET_REQUEST_PASSWORD_RESET_ACTIONS_LOGIN) ?? Heroicon::ArrowLeft,
            })
            ->url(filament()->getLoginUrl());
    }

    public function getTitle(): string|Htmlable
    {
        return 'Passwort vergessen';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Einmal-Anmeldung per E-Mail';
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getRequestFormAction(),
        ];
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label('Link senden')
            ->submit('request');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (! filament()->hasLogin()) {
            return null;
        }

        return $this->loginAction;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_BEFORE),
                $this->getFormContentComponent(),
                RenderHook::make(PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('request')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }
}
