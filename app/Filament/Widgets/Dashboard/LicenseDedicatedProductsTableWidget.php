<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\LicenseProducts\LicenseProductResource;
use App\Services\Dashboard\LicenseUsageDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LicenseDedicatedProductsTableWidget extends TableWidget
{
    protected static ?int $sort = 59;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Dedicated-Lizenzprodukte';

    public function table(Table $table): Table
    {
        return $table
            ->query(LicenseUsageDashboardQuery::dedicatedProductsQuery())
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('name')->label('Produkt'),
                TextColumn::make('total_available_licenses')->label('Kontingent')->numeric(),
                TextColumn::make('used_count')->label('Verwendet')->numeric(),
            ])
            ->recordUrl(fn (LicenseProduct $record): string => LicenseProductResource::getUrl('edit', ['record' => $record]));
    }
}
