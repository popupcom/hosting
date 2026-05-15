<?php

namespace App\Filament\Pages;

use App\Models\DesignSetting;
use App\Rules\SafeCustomCss;
use App\Support\NotificationStyleTokens;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Throwable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class GestaltungSettingsPage extends Page
{
    use CanUseDatabaseTransactions;

    protected static ?string $title = 'Gestaltung';

    protected static ?string $navigationLabel = 'Gestaltung';

    protected static string|UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 99999;

    protected static ?string $slug = 'gestaltung';

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
            ...$setting->only([
                'app_name',
                'primary_color',
                'secondary_color',
                'accent_color',
                'background_color',
                'text_color',
                'border_radius',
                'logo_path',
                'favicon_path',
                'custom_css',
                'design_notes',
            ]),
            'notification_style' => $setting->resolvedNotificationTokens(),
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
        $hexColorRule = ['nullable', 'string', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/'];

        return $schema
            ->components([
                Section::make('Marke & Text')
                    ->columns(2)
                    ->schema([
                        TextInput::make('app_name')
                            ->label('App-Name')
                            ->maxLength(255)
                            ->required(),
                    ]),
                Section::make('Farben')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('Primärfarbe')
                            ->hex()
                            ->live()
                            ->rules($hexColorRule),
                        ColorPicker::make('secondary_color')
                            ->label('Sekundärfarbe')
                            ->hex()
                            ->live()
                            ->rules($hexColorRule),
                        ColorPicker::make('accent_color')
                            ->label('Akzentfarbe')
                            ->hex()
                            ->live()
                            ->rules($hexColorRule),
                        ColorPicker::make('background_color')
                            ->label('Hintergrundfarbe')
                            ->hex()
                            ->live()
                            ->rules($hexColorRule),
                        ColorPicker::make('text_color')
                            ->label('Textfarbe')
                            ->hex()
                            ->live()
                            ->rules($hexColorRule),
                    ]),
                Section::make('Logo & Favicon')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->maxFiles(1)
                            ->maxSize(2048)
                            ->helperText('PNG, JPG, SVG … max. 2 MB. Öffentlich unter /storage/branding — dafür `php artisan storage:link` ausführen, falls der Header das Bild nicht lädt.'),
                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->maxFiles(1)
                            ->maxSize(512)
                            ->acceptedFileTypes([
                                'image/png',
                                'image/jpeg',
                                'image/gif',
                                'image/webp',
                                'image/svg+xml',
                                'image/x-icon',
                                'image/vnd.microsoft.icon',
                            ])
                            ->helperText('PNG, ICO, SVG o. ä.; maximal 512 KB.'),
                        Placeholder::make('logo_preview')
                            ->label('Aktuelles Logo (gespeichert)')
                            ->content(function (): Htmlable {
                                $url = DesignSetting::current()->resolvedLogoPublicUrl();
                                if ($url === null) {
                                    return new HtmlString('<span class="text-gray-500">Kein Logo (weder Upload noch Standard unter public/images/brand).</span>');
                                }

                                return new HtmlString(
                                    '<img src="'.e($url).'" alt="Logo" style="max-height:3rem;width:auto;object-fit:contain;" />',
                                );
                            })
                            ->columnSpanFull(),
                        Placeholder::make('favicon_preview')
                            ->label('Aktuelles Favicon (gespeichert)')
                            ->content(function (): Htmlable {
                                $url = DesignSetting::current()->resolvedFaviconPublicUrl();
                                if ($url === null) {
                                    return new HtmlString('<span class="text-gray-500">Kein Favicon hinterlegt.</span>');
                                }

                                return new HtmlString(
                                    '<img src="'.e($url).'" alt="Favicon" style="height:2rem;width:2rem;object-fit:contain;" />',
                                );
                            })
                            ->columnSpanFull(),
                    ]),
                Section::make('Layout')
                    ->schema([
                        TextInput::make('border_radius')
                            ->label('Border-Radius')
                            ->maxLength(64)
                            ->helperText('z. B. 0.75rem, 12px, 9999px')
                            ->live(onBlur: true)
                            ->rules([
                                'nullable',
                                'string',
                                'max:64',
                                'regex:/^[a-z0-9.\-\s%(),]+$/i',
                            ]),
                    ]),
                Section::make('Benachrichtigungsseiten')
                    ->description('Gestaltung für „Benachrichtigungen“ und „Meine Benachrichtigungen“ (Filter, Matrix, Karten, Toggle).')
                    ->columns(2)
                    ->schema(self::notificationStyleFormFields())
                    ->collapsed(),
                Section::make('Vorschau Benachrichtigungen')
                    ->description('Live-Vorschau der Formularwerte.')
                    ->schema([
                        Placeholder::make('notification_style_preview')
                            ->label('')
                            ->content(function (Get $get): Htmlable {
                                $primary = $get('primary_color') ?: DesignSetting::defaultAttributes()['primary_color'];

                                return new HtmlString(view('filament.pages.partials.notification-style-preview', [
                                    'tokens' => NotificationStyleTokens::resolve(
                                        is_array($get('notification_style')) ? $get('notification_style') : null,
                                        (string) $primary,
                                    ),
                                ])->render());
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
                Section::make('CSS & Gestaltungsdokument')
                    ->columns(1)
                    ->schema([
                        Textarea::make('custom_css')
                            ->label('Custom CSS')
                            ->rows(12)
                            ->extraInputAttributes(['style' => 'font-family: ui-monospace, monospace; font-size: 0.8125rem;'])
                            ->helperText('Keine @import- oder externen url(http…)-Angaben. Keine script-Tags.')
                            ->rules(['nullable', 'string', 'max:50000', new SafeCustomCss]),
                        Textarea::make('design_notes')
                            ->label('Gestaltungsnotizen / zentrales Gestaltungsdokument')
                            ->rows(20)
                            ->rules(['nullable', 'string', 'max:100000']),
                    ]),
                Section::make('Vorschau')
                    ->description('Live anhand der Formularwerte (Farben & Radius).')
                    ->schema([
                        Placeholder::make('design_preview')
                            ->label('')
                            ->content(function (Get $get): Htmlable {
                                $defaults = DesignSetting::defaultAttributes();

                                return new HtmlString(view('filament.pages.partials.design-preview', [
                                    'primary' => $get('primary_color') ?: $defaults['primary_color'],
                                    'secondary' => $get('secondary_color') ?: $defaults['secondary_color'],
                                    'accent' => $get('accent_color') ?: $defaults['accent_color'],
                                    'background' => $get('background_color') ?: $defaults['background_color'],
                                    'text' => $get('text_color') ?: $defaults['text_color'],
                                    'radius' => $get('border_radius') ?: $defaults['border_radius'],
                                ])->render());
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return list<ColorPicker|TextInput>
     */
    protected static function notificationStyleFormFields(): array
    {
        $hexColorRule = ['nullable', 'string', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/'];
        $cssColorRule = ['nullable', 'string', 'max:128', 'regex:/^(#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})|rgba?\([^)]+\))$/i'];
        $cssSizeRule = ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9.\-\s%(),]+$/i'];

        $fields = [];

        foreach (NotificationStyleTokens::fieldDefinitions() as $key => $definition) {
            $fieldName = "notification_style.{$key}";

            if ($definition['type'] === 'text' && $key === 'matrix_max_height') {
                $fields[] = TextInput::make($fieldName)
                    ->label($definition['label'])
                    ->helperText($definition['helper'])
                    ->live(onBlur: true)
                    ->rules($cssSizeRule);

                continue;
            }

            if ($definition['type'] === 'text') {
                $fields[] = TextInput::make($fieldName)
                    ->label($definition['label'])
                    ->helperText($definition['helper'])
                    ->live(onBlur: true)
                    ->rules($cssColorRule);

                continue;
            }

            $fields[] = ColorPicker::make($fieldName)
                ->label($definition['label'])
                ->helperText($definition['helper'])
                ->hex()
                ->live()
                ->rules($hexColorRule);
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
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Gestaltung';
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            foreach (['logo_path', 'favicon_path'] as $fileField) {
                if (! array_key_exists($fileField, $data)) {
                    continue;
                }
                $v = $data[$fileField];
                if (is_array($v)) {
                    $data[$fileField] = $v[0] ?? null;
                }
            }

            $record = DesignSetting::current();
            $record->fill($data);
            $record->updated_by = Auth::id();
            $record->save();

            DesignSetting::forgetRememberedInstance();

            $this->callHook('afterSave');
        } catch (Halt) {
            $this->rollBackDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        Notification::make()
            ->title('Gestaltung gespeichert')
            ->success()
            ->send();

        $this->fillForm();
    }
}
