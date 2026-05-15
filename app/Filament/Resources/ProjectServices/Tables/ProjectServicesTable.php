<?php

namespace App\Filament\Resources\ProjectServices\Tables;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\ProjectServices\ProjectServiceResource;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\Client;
use App\Models\Project;
use App\Services\ProjectServices\ProjectServiceSnapshotter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'project.client',
                'serviceCatalogItem',
                'billingGroup',
            ]))
            ->defaultSort('next_renewal_date')
            ->columns([
                TextColumn::make('project.client.name')
                    ->label('Kund:in')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): string => ProjectResource::getUrl('edit', ['record' => $record->project_id])),
                TextColumn::make('effective_name')
                    ->label('Leistung')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search): void {
                            $q->where('custom_name', 'like', "%{$search}%")
                                ->orWhere('name_snapshot', 'like', "%{$search}%")
                                ->orWhereHas('serviceCatalogItem', fn (Builder $sq) => $sq->where('name', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('serviceCatalogItem.category')
                    ->label('Kategorie')
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogCategory(
                        $state instanceof ServiceCatalogCategory
                            ? $state
                            : ServiceCatalogCategory::tryFrom((string) $state),
                    )),
                TextColumn::make('effective_quantity')
                    ->label('Menge')
                    ->numeric(decimalPlaces: 4, decimalSeparator: ',', thousandsSeparator: '.'),
                TextColumn::make('effective_cost_price')
                    ->label('EK')
                    ->money('EUR')
                    ->toggleable(),
                TextColumn::make('effective_sales_price')
                    ->label('VK')
                    ->money('EUR'),
                TextColumn::make('margin_amount')
                    ->label('Marge')
                    ->money('EUR')
                    ->color(fn ($record): string => ($record->margin_amount ?? 0) < 0 ? 'danger' : 'success'),
                TextColumn::make('margin_percentage')
                    ->label('Marge %')
                    ->formatStateUsing(fn ($state): string => $state !== null ? number_format((float) $state, 1, ',', '.').' %' : '—'),
                TextColumn::make('effective_billing_interval')
                    ->label('Intervall')
                    ->formatStateUsing(fn ($state): string => GermanLabels::serviceCatalogBillingInterval(
                        $state instanceof ServiceCatalogBillingInterval
                            ? $state
                            : ServiceCatalogBillingInterval::tryFrom((string) $state),
                    )),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?ProjectServiceStatus $state): string => GermanLabels::projectServiceStatus($state))
                    ->color(fn ($record): string => StatusBadge::projectService($record->status)),
                TextColumn::make('next_renewal_date')
                    ->label('Nächste Verlängerung')
                    ->date()
                    ->sortable(),
                TextColumn::make('cancellation_date')
                    ->label('Kündigung')
                    ->date()
                    ->sortable(),
                IconColumn::make('do_not_renew')
                    ->label('Keine Verlängerung')
                    ->boolean(),
                TextColumn::make('billingGroup.name')
                    ->label('Verrechnungsgruppe')
                    ->placeholder('—'),
                TextColumn::make('moco_sync_status')
                    ->label('Moco')
                    ->badge()
                    ->formatStateUsing(fn (?ProjectServiceMocoSyncStatus $state): string => GermanLabels::projectServiceMocoSyncStatus($state))
                    ->color(fn ($record): string => StatusBadge::projectServiceMoco($record->moco_sync_status)),
            ])
            ->filters([
                SelectFilter::make('project.client_id')
                    ->label('Kund:in')
                    ->options(fn (): array => Client::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('project', fn (Builder $q) => $q->where('client_id', $data['value']))
                        : $query),
                SelectFilter::make('project_id')
                    ->label('Projekt')
                    ->options(fn (): array => Project::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('service_catalog_item_id')
                    ->label('Katalog-Leistung')
                    ->relationship('serviceCatalogItem', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::projectServiceStatuses())
                    ->multiple(),
                SelectFilter::make('custom_billing_interval')
                    ->label('Intervall (individuell)')
                    ->options(GermanLabels::serviceCatalogBillingIntervals()),
                TernaryFilter::make('do_not_renew')->label('Nicht verlängern'),
                SelectFilter::make('billing_group_id')
                    ->label('Verrechnungsgruppe')
                    ->relationship('billingGroup', 'name'),
                SelectFilter::make('moco_sync_status')
                    ->label('Moco')
                    ->options(GermanLabels::projectServiceMocoSyncStatuses()),
                Filter::make('ending_soon')
                    ->label('Endet in 60 Tagen')
                    ->query(fn (Builder $query): Builder => $query->endingSoon(60)),
                Filter::make('cancellation_date')
                    ->schema([
                        DatePicker::make('from')->label('Kündigung ab'),
                        DatePicker::make('until')->label('Kündigung bis'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $v) => $q->whereDate('cancellation_date', '>=', $v))
                            ->when($data['until'] ?? null, fn (Builder $q, $v) => $q->whereDate('cancellation_date', '<=', $v));
                    }),
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
                    ->action(function ($record, array $data): void {
                        ProjectServiceSnapshotter::markCancelled($record, $data['reason'] ?? null);
                        $record->save();
                    })
                    ->form([
                        Textarea::make('reason')
                            ->label('Kündigungsgrund')
                            ->rows(2),
                    ]),
                Action::make('doNotRenew')
                    ->label('Nicht verlängern')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['do_not_renew' => true, 'renews_automatically' => false])),
            ])
            ->recordUrl(fn ($record): string => ProjectServiceResource::getUrl('edit', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
