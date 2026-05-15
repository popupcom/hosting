<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\LicenseSharingModel;
use App\Filament\Resources\LicenseProducts\LicenseProductResource;
use App\Filament\Support\GermanLabels;
use App\Models\LicenseProduct;
use App\Services\Dashboard\LicenseUsageDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LicenseHighUtilizationProductsTableWidget extends TableWidget
{
    protected static ?int $sort = 57;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Lizenzprodukte mit hoher Auslastung (≥ 80 %)';

    public function table(Table $table): Table
    {
        return $table
            ->query(LicenseUsageDashboardQuery::highUtilizationProductsQuery(80))
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('name')->label('Produkt')->searchable(),
                TextColumn::make('license_model')
                    ->label('Modell')
                    ->badge()
                    ->formatStateUsing(fn (?LicenseSharingModel $state): string => GermanLabels::licenseSharingModel($state)),
                TextColumn::make('total_available_licenses')->label('Kontingent')->numeric(),
                TextColumn::make('used_count')->label('Verwendet')->numeric(),
                TextColumn::make('utilization')
                    ->label('Auslastung')
                    ->state(fn (LicenseProduct $record): string => ($p = $record->utilizationPercent()) !== null ? $p.' %' : '—')
                    ->color('warning'),
            ])
            ->recordUrl(fn (LicenseProduct $record): string => LicenseProductResource::getUrl('edit', ['record' => $record]));
    }
}
