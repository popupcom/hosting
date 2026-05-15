<?php

namespace App\Filament\Pages;

use App\Enums\NotificationEventCategory;
use App\Models\NotificationEventType;
use App\Models\NotificationGroup;
use App\Models\NotificationGroupEventSetting;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class NotificationSettingsPage extends Page
{
    protected static ?string $title = 'Benachrichtigungen';

    protected static ?string $navigationLabel = 'Benachrichtigungen';

    protected static string|UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static ?int $navigationSort = 99994;

    protected static ?string $slug = 'benachrichtigungen';

    protected string $view = 'filament.pages.notification-settings';

    public ?string $categoryFilter = null;

    /** @var array<string, array{enabled: bool, email: bool, in_app: bool}> */
    public array $matrix = [];

    /** @var array<string, mixed> */
    public array $newGroup = ['name' => '', 'description' => ''];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isAdmin();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->loadMatrix();
    }

    public function getHeading(): string|Htmlable
    {
        return 'Benachrichtigungen';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Steuern Sie Gruppen, Ereignisse und Kanäle (E-Mail / In-App) für das gesamte Team.';
    }

    public function loadMatrix(): void
    {
        $this->matrix = [];

        $settings = NotificationGroupEventSetting::query()->get()->keyBy(
            fn (NotificationGroupEventSetting $s): string => $s->notification_group_id.'_'.$s->notification_event_type_id,
        );

        foreach (NotificationGroup::query()->where('is_active', true)->pluck('id') as $groupId) {
            foreach (NotificationEventType::query()->where('is_active', true)->pluck('id') as $eventId) {
                $key = $groupId.'_'.$eventId;
                $setting = $settings->get($key);
                $this->matrix[$key] = [
                    'enabled' => (bool) ($setting?->is_enabled ?? false),
                    'email' => (bool) ($setting?->send_email ?? false),
                    'in_app' => (bool) ($setting?->send_in_app ?? false),
                ];
            }
        }
    }

    public function saveMatrix(): void
    {
        foreach ($this->matrix as $key => $state) {
            if (! str_contains($key, '_')) {
                continue;
            }
            [$groupId, $eventId] = explode('_', $key, 2);

            NotificationGroupEventSetting::query()->updateOrCreate(
                [
                    'notification_group_id' => (int) $groupId,
                    'notification_event_type_id' => (int) $eventId,
                ],
                [
                    'is_enabled' => (bool) ($state['enabled'] ?? false),
                    'send_email' => (bool) ($state['email'] ?? false),
                    'send_in_app' => (bool) ($state['in_app'] ?? false),
                ],
            );
        }

        Notification::make()->title('Gruppen-Einstellungen gespeichert')->success()->send();
    }

    public function createGroup(): void
    {
        $name = trim((string) ($this->newGroup['name'] ?? ''));
        if ($name === '') {
            Notification::make()->title('Gruppenname erforderlich')->danger()->send();

            return;
        }

        $group = NotificationGroup::query()->create([
            'name' => $name,
            'description' => $this->newGroup['description'] ?? null,
            'is_active' => true,
        ]);

        foreach (NotificationEventType::query()->pluck('id') as $eventId) {
            NotificationGroupEventSetting::query()->create([
                'notification_group_id' => $group->id,
                'notification_event_type_id' => $eventId,
                'is_enabled' => false,
                'send_email' => false,
                'send_in_app' => false,
            ]);
        }

        $this->newGroup = ['name' => '', 'description' => ''];
        $this->loadMatrix();

        Notification::make()->title('Gruppe angelegt')->success()->send();
    }

    /**
     * @return Collection<int, NotificationGroup>
     */
    public function getGroupsProperty()
    {
        return NotificationGroup::query()->where('is_active', true)->orderBy('name')->get();
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('myPreferences')
                ->label('Meine Einstellungen')
                ->icon(Heroicon::OutlinedUser)
                ->url(fn (): string => MyNotificationPreferencesPage::getUrl()),
        ];
    }
}
