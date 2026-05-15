<?php

namespace App\Filament\Pages;

use App\Enums\NotificationEventCategory;
use App\Models\NotificationEventType;
use App\Models\User;
use App\Models\UserNotificationPreference;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MyNotificationPreferencesPage extends Page
{
    protected static ?string $title = 'Meine Benachrichtigungen';

    protected static ?string $navigationLabel = 'Meine Benachrichtigungen';

    protected static string|UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 99995;

    protected static ?string $slug = 'meine-benachrichtigungen';

    protected string $view = 'filament.pages.my-notification-preferences';

    public ?string $categoryFilter = null;

    /** @var array<string, array{enabled: bool, email: bool, in_app: bool}> */
    public array $preferences = [];

    public function mount(): void
    {
        $this->loadPreferences();
    }

    public function getHeading(): string|Htmlable
    {
        return 'Meine Benachrichtigungen';
    }

    public function loadPreferences(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }

        $this->preferences = [];

        $existing = UserNotificationPreference::query()
            ->where('user_id', $user->getKey())
            ->get()
            ->keyBy('notification_event_type_id');

        foreach (NotificationEventType::query()->where('is_active', true)->orderBy('name')->get() as $event) {
            $pref = $existing->get($event->id);
            $this->preferences[(string) $event->id] = [
                'enabled' => $pref?->is_enabled ?? false,
                'email' => $pref?->email_enabled ?? true,
                'in_app' => $pref?->in_app_enabled ?? true,
            ];
        }
    }

    public function savePreferences(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }

        foreach ($this->preferences as $eventId => $state) {
            UserNotificationPreference::query()->updateOrCreate(
                [
                    'user_id' => $user->getKey(),
                    'notification_event_type_id' => (int) $eventId,
                ],
                [
                    'is_enabled' => (bool) ($state['enabled'] ?? false),
                    'email_enabled' => (bool) ($state['email'] ?? false),
                    'in_app_enabled' => (bool) ($state['in_app'] ?? false),
                ],
            );
        }

        Notification::make()->title('Einstellungen gespeichert')->success()->send();
    }

    /**
     * @return Collection<int, NotificationEventType>
     */
    public function getEventTypesProperty()
    {
        return NotificationEventType::query()
            ->where('is_active', true)
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function getCategoryOptionsProperty(): array
    {
        return NotificationEventCategory::labels();
    }
}
