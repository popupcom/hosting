<?php

namespace App\Filament\Pages;

use App\Models\DesignSetting;
use App\Support\UiLabelCatalog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class LocalizationSettingsPage extends Page
{
    protected static ?string $title = 'Sprache & Texte';

    protected static ?string $navigationLabel = 'Sprache & Texte';

    protected static string|UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = 99998;

    protected static ?string $slug = 'sprache-uebersetzungen';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->isAdmin();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->fillForm();
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    protected function fillForm(): void
    {
        $setting = DesignSetting::current();

        $this->form->fill([
            'ui_locale' => $setting->effectiveUiLocale(),
            'ui_label_overrides' => $setting->resolvedUiLabelOverrides(),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->operation('edit')
            ->model(DesignSetting::current())
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Anzeigesprache')
                    ->description('Steuert Laravel- und Filament-Basis-Texte (Menüs, Buttons, Validierung).')
                    ->schema([
                        Select::make('ui_locale')
                            ->label('Sprache')
                            ->options([
                                'de' => 'Deutsch',
                                'en' => 'English',
                            ])
                            ->native(false)
                            ->required()
                            ->rules(['required', 'in:de,en']),
                    ]),
                self::uiLabelEditorSection(),
            ]);
    }

    protected static function uiLabelEditorSection(): Section
    {
        $groupOptions = UiLabelCatalog::groupedSelectOptions();
        $firstGroupKey = array_key_first(
            array_merge(...array_values($groupOptions)),
        );

        return Section::make('Fachtexte im Tool')
            ->description('Gruppiert nach Stammdaten, Leistungskatalog (inkl. Lizenzen), Support (Pakete, Wartung, ToDos) und Abrechnung.')
            ->schema([
                Select::make('_editing_label_group')
                    ->label('Bereich')
                    ->options($groupOptions)
                    ->default($firstGroupKey)
                    ->native(false)
                    ->live()
                    ->dehydrated(false)
                    ->searchable(),
                ...array_map(
                    static fn (array $group, string $groupKey): Section => Section::make($group['title'])
                        ->schema(self::fieldsForLabelGroup($groupKey))
                        ->columns(2)
                        ->visible(fn (Get $get): bool => ($get('_editing_label_group') ?: $firstGroupKey) === $groupKey),
                    UiLabelCatalog::groups(),
                    array_keys(UiLabelCatalog::groups()),
                ),
            ]);
    }

    /**
     * @return list<TextInput>
     */
    protected static function fieldsForLabelGroup(string $groupKey): array
    {
        $group = UiLabelCatalog::groups()[$groupKey] ?? null;

        if ($group === null) {
            return [];
        }

        $fields = [];

        foreach ($group['fields'] as $field) {
            $fields[] = TextInput::make("ui_label_overrides.{$groupKey}.{$field['key']}")
                ->label($field['label'])
                ->helperText('Standard: '.$field['default'])
                ->maxLength(255)
                ->placeholder($field['default']);
        }

        return $fields;
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
            ->id('localization-form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(Alignment::Start)
                    ->key('localization-form-actions'),
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
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Sprache & Texte';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Status-, Katalog- und ToDo-Texte im Tool anpassen. Leer lassen = Standardtext.';
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->callHook('beforeValidate');

        $data = array_merge($this->form->getState(), is_array($this->data) ? $this->data : []);

        $this->callHook('afterValidate');

        $locale = $data['ui_locale'] ?? 'de';
        if (! is_string($locale) || ! in_array($locale, ['de', 'en'], true)) {
            $locale = 'de';
        }

        $record = DesignSetting::current();
        $overrides = is_array($data['ui_label_overrides'] ?? null) ? $data['ui_label_overrides'] : [];

        foreach ($overrides as $group => $labels) {
            if (! is_array($labels)) {
                continue;
            }

            foreach ($labels as $key => $value) {
                if (is_string($value) && trim($value) === '') {
                    unset($overrides[$group][$key]);
                }
            }

            if ($overrides[$group] === []) {
                unset($overrides[$group]);
            }
        }

        $record->fill([
            'ui_locale' => $locale,
            'ui_label_overrides' => $overrides,
        ]);
        $record->updated_by = Auth::id();
        $record->save();

        DesignSetting::forgetRememberedInstance();

        $this->callHook('afterSave');

        Notification::make()
            ->title('Sprache & Texte gespeichert')
            ->body('Änderungen gelten sofort im Tool. Bei Filament-Basis-Texten ggf. Seite neu laden.')
            ->success()
            ->send();

        $this->fillForm();
    }
}
