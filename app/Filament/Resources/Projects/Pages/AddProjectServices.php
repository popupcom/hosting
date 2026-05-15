<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Support\GermanLabels;
use App\Models\Project;
use App\Models\ServiceCatalogItem;
use App\Services\ProjectServices\BulkProjectServiceCreator;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @property-read Schema $form
 */
class AddProjectServices extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProjectResource::class;

    protected static ?string $title = 'Leistungen hinzufügen';

    protected static ?string $navigationLabel = 'Leistungen hinzufügen';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountCanAuthorizeAccess();
        $this->form->fill();
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Leistungen hinzufügen: '.$this->getProject()->name;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Katalog-Leistungen')
                    ->description('Bereits aktive oder vorgemerkte Leistungen desselben Katalogeintrags werden übersprungen.')
                    ->schema([
                        CheckboxList::make('service_catalog_item_ids')
                            ->label('Leistungen auswählen')
                            ->options(fn (): array => BulkProjectServiceCreator::catalogOptions()
                                ->mapWithKeys(fn (ServiceCatalogItem $item): array => [
                                    $item->getKey() => trim(
                                        ($item->category
                                            ? GermanLabels::serviceCatalogCategory($item->category).' · '
                                            : ''
                                        ).$item->name
                                    ),
                                ])
                                ->all())
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->required(),
                    ]),
                Actions::make([
                    Action::make('submit')
                        ->label('Leistungen anlegen')
                        ->icon(Heroicon::OutlinedPlus)
                        ->action('createServices'),
                    Action::make('cancel')
                        ->label('Abbrechen')
                        ->color('gray')
                        ->url(fn (): string => ProjectResource::getUrl('edit', ['record' => $this->getProject()])),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('createServices'),
            ]);
    }

    public function createServices(): void
    {
        $state = $this->form->getState();
        $ids = array_map('intval', $state['service_catalog_item_ids'] ?? []);

        $result = BulkProjectServiceCreator::createForProject($this->getProject(), $ids);

        Notification::make()
            ->title('Leistungen angelegt')
            ->body(sprintf(
                '%d angelegt, %d übersprungen (bereits vorhanden oder ungültig).',
                $result['created'],
                $result['skipped'],
            ))
            ->success()
            ->send();

        $this->redirect(ProjectResource::getUrl('edit', ['record' => $this->getProject()]));
    }

    protected function getProject(): Project
    {
        /** @var Project $project */
        $project = $this->getRecord();

        return $project;
    }
}
