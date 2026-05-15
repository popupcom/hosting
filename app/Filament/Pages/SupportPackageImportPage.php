<?php

namespace App\Filament\Pages;

use App\Services\Imports\SupportPackageExcelImporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SupportPackageImportPage extends Page
{
    protected static ?string $title = 'Supportpakete-Import';

    protected static ?string $navigationLabel = 'Import';

    protected static string|UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?int $navigationSort = 99996;

    protected static ?string $slug = 'import-supportpakete';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    /** @var list<array<string, mixed>>|null */
    public ?array $previewRows = null;

    /** @var array<string, mixed>|null */
    public ?array $importSummary = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user !== null && $user->isAdmin();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->form->fill();
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Excel-Datei')
                    ->description('Blatt **Supportpakete Import** mit den Spalten laut Handbuch (`docs/support-package-excel-import.md`). Pflicht: `customer_name`, `website`.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Datei (.xlsx)')
                            ->disk('local')
                            ->directory('imports/excel')
                            ->visibility('private')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->maxSize(10240)
                            ->required()
                            ->helperText('Max. 10 MB. Es werden keine Passwörter oder Zahlungsdaten importiert.'),
                    ]),
                Section::make('Vorschau')
                    ->collapsed()
                    ->visible(fn (): bool => $this->previewRows !== null)
                    ->schema([
                        Placeholder::make('preview_table')
                            ->label('')
                            ->content(function (): Htmlable {
                                $rows = $this->previewRows ?? [];

                                return new HtmlString(view('filament.pages.support-package-import-preview', [
                                    'rows' => $rows,
                                ])->render());
                            }),
                    ]),
                Section::make('Letztes Import-Log')
                    ->collapsed()
                    ->visible(fn (): bool => $this->importSummary !== null)
                    ->schema([
                        Placeholder::make('import_log')
                            ->label('')
                            ->content(function (): Htmlable {
                                $r = $this->importSummary;
                                if ($r === null) {
                                    return new HtmlString('');
                                }

                                return new HtmlString(view('filament.pages.support-package-import-log', [
                                    'summary' => $r,
                                ])->render());
                            }),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('import-form')
                    ->footer([
                        Actions::make($this->getFormFooterActions())
                            ->alignment(Alignment::Start)
                            ->key('import-form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormFooterActions(): array
    {
        return [
            Action::make('preview')
                ->label('Vorschau laden')
                ->color('gray')
                ->action('loadPreview'),
            Action::make('import')
                ->label('Import ausführen')
                ->color('primary')
                ->action('runImport'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Supportpakete-Import (Excel)';
    }

    public function loadPreview(): void
    {
        $state = $this->form->getState();
        $relative = $state['file'] ?? null;
        if (blank($relative)) {
            Notification::make()->title('Bitte zuerst eine Datei wählen.')->warning()->send();

            return;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists((string) $relative)) {
            Notification::make()->title('Datei nicht gefunden.')->danger()->send();

            return;
        }

        $path = $disk->path((string) $relative);

        try {
            $importer = app(SupportPackageExcelImporter::class);
            $raw = $importer->peek($path, 30);
            $this->previewRows = array_map(static function (array $r) use ($importer): array {
                $errs = $importer->rowPreviewErrors($r);
                $r['prüfung'] = $errs === [] ? 'OK' : implode('; ', $errs);

                return $r;
            }, $raw);
            Notification::make()
                ->title('Vorschau geladen')
                ->body(count($this->previewRows).' Zeile(n).')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->previewRows = null;
            Notification::make()
                ->title('Vorschau fehlgeschlagen')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function runImport(): void
    {
        $state = $this->form->getState();
        $relative = $state['file'] ?? null;
        if (blank($relative)) {
            Notification::make()->title('Bitte zuerst eine Datei wählen.')->warning()->send();

            return;
        }

        $disk = Storage::disk('local');
        if (! $disk->exists((string) $relative)) {
            Notification::make()->title('Datei nicht gefunden.')->danger()->send();

            return;
        }

        $path = $disk->path((string) $relative);

        try {
            $result = app(SupportPackageExcelImporter::class)->import($path);
            $this->importSummary = [
                'clientsCreated' => $result->clientsCreated,
                'clientsUpdated' => $result->clientsUpdated,
                'projectsCreated' => $result->projectsCreated,
                'projectsUpdated' => $result->projectsUpdated,
                'projectServicesCreated' => $result->projectServicesCreated,
                'projectServicesUpdated' => $result->projectServicesUpdated,
                'maintenanceHistoriesCreated' => $result->maintenanceHistoriesCreated,
                'maintenanceHistoriesUpdated' => $result->maintenanceHistoriesUpdated,
                'catalogItemsCreated' => $result->catalogItemsCreated,
                'errorCount' => $result->errorCount,
                'errors' => $result->errors,
            ];
            Notification::make()
                ->title('Import abgeschlossen')
                ->body($result->errorCount > 0
                    ? 'Mit '.$result->errorCount.' Zeilenfehler(n).'
                    : 'Ohne Fehler.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import fehlgeschlagen')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
