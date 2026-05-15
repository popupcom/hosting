<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Enums\ProjectSupportPackageStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\MaintenanceHistory;
use App\Models\ProjectSupportPackage;
use App\Models\SupportPackage;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectSupportPackagesRelationManager extends RelationManager
{
    protected static string $relationship = 'projectSupportPackages';

    protected static ?string $title = 'Supportpaket';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supportpaket zuweisen')
                    ->description('Erzeugt automatisch eine Projekt-Leistung aus dem Leistungskatalog (jährlich im Voraus).')
                    ->columns(2)
                    ->schema([
                        Select::make('support_package_id')
                            ->label('Supportpaket')
                            ->relationship(
                                name: 'supportPackage',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->activeCatalog(),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->native(false),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::projectSupportPackageStatuses())
                            ->default(ProjectSupportPackageStatus::Active->value)
                            ->required()
                            ->native(false),
                        DatePicker::make('start_date')
                            ->label('Startdatum')
                            ->default(now())
                            ->required()
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label('Enddatum')
                            ->native(false),
                        DatePicker::make('cancellation_date')
                            ->label('Kündigungsdatum')
                            ->native(false),
                        Toggle::make('do_not_renew')->label('Nicht verlängern'),
                        Placeholder::make('catalog_prices')
                            ->label('Preise (aus Katalog)')
                            ->content(function (Get $get): string {
                                $package = SupportPackage::query()
                                    ->with('serviceCatalogItem')
                                    ->find($get('support_package_id'));

                                if ($package?->serviceCatalogItem === null) {
                                    return 'Paket wählen …';
                                }

                                $monthly = (float) $package->serviceCatalogItem->sales_price;
                                $yearly = $monthly * 12;

                                return sprintf(
                                    'Monatlich: %s € · Jährlich: %s €',
                                    number_format($monthly, 2, ',', '.'),
                                    number_format($yearly, 2, ',', '.'),
                                );
                            })
                            ->columnSpanFull(),
                        Placeholder::make('technical_features')
                            ->label('Enthaltene Leistungen')
                            ->content(function (Get $get): string {
                                $package = SupportPackage::query()->find($get('support_package_id'));
                                if ($package === null) {
                                    return '—';
                                }

                                $labels = $package->includedFeatureLabels();

                                return $labels !== [] ? implode(' · ', $labels) : 'Keine technischen Merkmale hinterlegt';
                            })
                            ->columnSpanFull(),
                        Placeholder::make('next_maintenance')
                            ->label('Nächste Wartung (Hinweis)')
                            ->content(function (): string {
                                $projectId = $this->getOwnerRecord()->getKey();
                                $next = MaintenanceHistory::query()
                                    ->where('project_id', $projectId)
                                    ->orderByDesc('performed_on')
                                    ->value('performed_on');

                                return $next
                                    ? 'Letzte Wartung: '.$next
                                    : 'Noch keine Wartung erfasst — ToDos/Wartungsprotokolle prüfen.';
                            })
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['supportPackage.serviceCatalogItem', 'projectService']))
            ->columns([
                TextColumn::make('supportPackage.name')
                    ->label('Paket')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supportPackage.serviceCatalogItem.name')
                    ->label('Katalog-Leistung')
                    ->toggleable(),
                TextColumn::make('yearly_vk')
                    ->label('Jährlicher VK')
                    ->state(function (ProjectSupportPackage $record): string {
                        $monthly = $record->supportPackage?->serviceCatalogItem?->sales_price;
                        if ($monthly === null) {
                            return '—';
                        }

                        return number_format((float) $monthly * 12, 2, ',', '.').' €';
                    }),
                TextColumn::make('start_date')
                    ->label('Start')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Ende')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProjectSupportPackageStatus $state): string => GermanLabels::projectSupportPackageStatus($state))
                    ->color(fn (ProjectSupportPackage $record): string => StatusBadge::projectSupportPackage($record->status)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['project_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
