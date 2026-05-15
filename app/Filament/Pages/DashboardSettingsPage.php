<?php

namespace App\Filament\Pages;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Filament\Support\DashboardWidgetRegistry;
use App\Filament\Support\GermanLabels;
use App\Models\Client;
use App\Models\DashboardPreference;
use App\Models\Project;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\SimplePage;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read Schema $form
 */
class DashboardSettingsPage extends Page
{
    protected static ?string $slug = 'dashboard-einstellungen';

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected string $view = 'filament-panels::pages.simple';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function getLabel(): string
    {
        return 'Dashboard-Einstellungen';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Dashboard-Einstellungen';
    }

    public static function canView(): bool
    {
        return Auth::check();
    }

    /**
     * Required by {@see SimplePage} / `filament-panels::pages.simple` when not extending SimplePage.
     */
    public function hasLogo(): bool
    {
        return true;
    }

    public function mount(): void
    {
        abort_unless(static::canView(), 403);

        $pref = DashboardPreference::forUser();
        $order = $pref->resolvedWidgetOrder();

        $this->form->fill([
            'visible_widgets' => $pref->visible_widget_keys ?? $order,
            'widget_order_entries' => array_map(
                static fn (string $class): array => ['class' => $class],
                $order,
            ),
            'annualized_view' => $pref->annualized_view,
            'filters' => array_merge(
                DashboardPreference::defaultFilters(),
                $pref->filters ?? [],
            ),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $cadenceOptions = [
            ProjectServiceDashboardQuery::CADENCE_MONTHLY => 'Monatlich',
            ProjectServiceDashboardQuery::CADENCE_YEARLY => 'Jährlich',
            ProjectServiceDashboardQuery::CADENCE_ONE_TIME => 'Einmalig',
            ProjectServiceDashboardQuery::CADENCE_UNKNOWN => 'Unbekanntes Intervall',
        ];

        $mocoOptions = collect(ProjectServiceMocoSyncStatus::cases())
            ->mapWithKeys(fn (ProjectServiceMocoSyncStatus $s): array => [$s->value => GermanLabels::projectServiceMocoSyncStatus($s)])
            ->all();

        return $schema
            ->components([
                Section::make('Widgets')
                    ->columns(1)
                    ->schema([
                        CheckboxList::make('visible_widgets')
                            ->label('Sichtbare Widgets')
                            ->options(DashboardWidgetRegistry::labels())
                            ->columns(2)
                            ->helperText('Alle ausgewählt oder keine Auswahl: alle Widgets werden angezeigt.'),
                        Repeater::make('widget_order_entries')
                            ->label('Reihenfolge')
                            ->schema([
                                Hidden::make('class'),
                                Placeholder::make('title')
                                    ->label('Widget')
                                    ->content(function (Get $get): string {
                                        $class = (string) $get('class');

                                        return DashboardWidgetRegistry::labels()[$class] ?? $class;
                                    }),
                            ])
                            ->reorderable()
                            ->addable(false)
                            ->deletable(false),
                        Toggle::make('annualized_view')
                            ->label('Annualisierte Ansicht für Rhythmus-Widgets')
                            ->helperText('Monatliche Beträge werden mit Faktor 12 dargestellt; jährliche und einmalige Beträge bleiben unverändert.'),
                    ]),
                Section::make('Filter für das Dashboard')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('filters.date_from')
                            ->label('Erstellt ab')
                            ->native(false),
                        DatePicker::make('filters.date_to')
                            ->label('Erstellt bis')
                            ->native(false),
                        Select::make('filters.client_id')
                            ->label('Kund:in')
                            ->options(fn (): array => Client::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->nullable(),
                        Select::make('filters.project_id')
                            ->label('Projekt')
                            ->options(function (Get $get): array {
                                $clientId = $get('filters.client_id');

                                return Project::query()
                                    ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        CheckboxList::make('filters.service_categories')
                            ->label('Leistungskategorie')
                            ->options(GermanLabels::serviceCatalogCategories())
                            ->columns(2)
                            ->columnSpanFull(),
                        CheckboxList::make('filters.billing_cadences')
                            ->label('Verrechnungsintervall (Kategorisierung)')
                            ->options($cadenceOptions)
                            ->columns(2)
                            ->columnSpanFull(),
                        CheckboxList::make('filters.moco_sync_statuses')
                            ->label('Moco-Status')
                            ->options($mocoOptions)
                            ->columns(2)
                            ->columnSpanFull(),
                        Toggle::make('filters.is_active_only')
                            ->label('Nur aktive Leistungen')
                            ->inline(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(Alignment::Start)
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Speichern')
                ->submit('save'),
            Action::make('back')
                ->label('Zurück zum Dashboard')
                ->color('gray')
                ->url(ManagementDashboard::getUrl()),
        ];
    }

    public function save(): void
    {
        abort_unless(static::canView(), 403);

        try {
            $state = $this->form->getState();
            $allClasses = DashboardWidgetRegistry::widgetClasses();
            $visible = $state['visible_widgets'] ?? null;
            if (! is_array($visible) || $visible === [] || count($visible) === count($allClasses)) {
                $visible = null;
            } else {
                $visible = array_values(array_intersect($allClasses, $visible));
            }

            $order = collect($state['widget_order_entries'] ?? [])
                ->pluck('class')
                ->filter(fn (?string $c): bool => $c !== null && $c !== '' && in_array($c, $allClasses, true))
                ->values()
                ->all();

            $filters = array_merge(
                DashboardPreference::defaultFilters(),
                $state['filters'] ?? [],
            );

            $pref = DashboardPreference::forUser();
            $pref->forceFill([
                'visible_widget_keys' => $visible,
                'widget_order' => $order !== [] ? $order : null,
                'annualized_view' => (bool) ($state['annualized_view'] ?? false),
                'filters' => $filters,
            ])->save();

            Notification::make()
                ->title('Gespeichert')
                ->success()
                ->send();

            $this->redirect(ManagementDashboard::getUrl(), navigate: true);
        } catch (Halt) {
            return;
        }
    }
}
