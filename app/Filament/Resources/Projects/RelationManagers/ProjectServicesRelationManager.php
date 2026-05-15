<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Enums\ProjectServiceStatus;
use App\Filament\Resources\ProjectServices\Schemas\ProjectServiceForm;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Services\ProjectServices\ProjectServiceSnapshotter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'projectServices';

    protected static ?string $title = 'Leistungen';

    public function form(Schema $schema): Schema
    {
        return ProjectServiceForm::configure(
            $schema,
            includeProjectSelect: false,
            fixedProjectId: (int) $this->getOwnerRecord()->getKey(),
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['serviceCatalogItem', 'billingGroup']))
            ->columns([
                TextColumn::make('effective_name')
                    ->label('Leistung')
                    ->searchable(['custom_name', 'name_snapshot']),
                TextColumn::make('effective_quantity')
                    ->label('Menge')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('effective_sales_price')
                    ->label('VK')
                    ->money('EUR'),
                TextColumn::make('effective_cost_price')
                    ->label('EK')
                    ->money('EUR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('billingGroup.name')
                    ->label('Verrechnungsgruppe')
                    ->placeholder('—'),
                TextColumn::make('next_renewal_date')
                    ->label('Verlängerung')
                    ->date(),
                TextColumn::make('cancellation_date')
                    ->label('Kündigung')
                    ->date(),
                IconColumn::make('do_not_renew')
                    ->label('Keine Verl.')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => GermanLabels::projectServiceStatus($state))
                    ->color(fn ($record): string => StatusBadge::projectService($record->status)),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('cancel')
                    ->label('Kündigen')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => ! in_array($record->status, [
                        ProjectServiceStatus::Cancelled,
                        ProjectServiceStatus::Expired,
                    ], true))
                    ->form([
                        Textarea::make('reason')->label('Kündigungsgrund')->rows(2),
                    ])
                    ->action(function ($record, array $data): void {
                        ProjectServiceSnapshotter::markCancelled($record, $data['reason'] ?? null);
                        $record->save();
                    }),
            ]);
    }
}
