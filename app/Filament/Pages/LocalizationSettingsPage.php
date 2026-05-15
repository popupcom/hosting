<?php

namespace App\Filament\Pages;

use App\Models\DesignSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
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
                    ->description('Steuert Laravel- und Filament-Basis-Texte (Menüs, Buttons, Validierung). Eigene Fachbegriffe im Tool liegen in der zentralen Label-Datei (siehe unten).')
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
                Section::make('Zentrale Texte im Repository')
                    ->schema([
                        Placeholder::make('paths_help')
                            ->label('')
                            ->content(fn (): Htmlable => new HtmlString(
                                view('filament.pages.localization-reference')->render()
                            )),
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
        $record->fill([
            'ui_locale' => $locale,
        ]);
        $record->updated_by = Auth::id();
        $record->save();

        DesignSetting::forgetRememberedInstance();

        $this->callHook('afterSave');

        Notification::make()
            ->title('Spracheinstellung gespeichert')
            ->body('Seite neu laden, falls Texte noch nicht wechseln.')
            ->success()
            ->send();

        $this->fillForm();
    }
}
