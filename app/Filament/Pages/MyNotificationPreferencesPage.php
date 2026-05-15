<?php

namespace App\Filament\Pages;

use App\Enums\NotificationEventCategory;
use App\Models\NotificationEventType;
use App\Models\User;
use App\Models\UserNotificationPreference;
use BackedEnum;
use Filament\Actions\Action;
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

    public function getSubheading(): string|Htmlable|null
    {
        return 'Wählen Sie persönlich, welche Ereignisse Sie erhalten möchten – unabhängig von Ihrer Gruppenzugehörigkeit.';
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

    /**
     * @return Collection<string, Collection<int, NotificationEventType>>
     */
    public function getEventsGroupedByCategoryProperty(): Collection
    {
        return $this->eventTypes->groupBy(
            fn (NotificationEventType $event): string => $event->category->value,
        );
    }

    public function categoryLabel(string $category): string
    {
        return NotificationEventCategory::tryFrom($category)?->label() ?? $category;
    }

    public function enabledCountForCategory(string $category): int
    {
        return $this->eventTypes
            ->filter(fn (NotificationEventType $event): bool => $event->category->value === $category)
            ->filter(fn (NotificationEventType $event): bool => (bool) ($this->preferences[(string) $event->id]['enabled'] ?? false))
            ->count();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->isAdmin()) {
            return [];
        }

        return [
            Action::make('groupSettings')
                ->label('Gruppen-Einstellungen')
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->url(fn (): string => NotificationSettingsPage::getUrl()),
        ];
    }
}
