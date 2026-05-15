<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\LicenseProducts\LicenseProductResource;
use App\Models\LicenseProduct;
use App\Services\Dashboard\LicenseUsageDashboardQuery;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LicenseSharedProductsTableWidget extends TableWidget
{
    protected static ?int $sort = 58;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Shared-Lizenzprodukte';

    public function table(Table $table): Table
    {
        return $table
            ->query(LicenseUsageDashboardQuery::sharedProductsQuery())
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('name')->label('Produkt'),
                TextColumn::make('used_count')->label('Nutzungen')->numeric(),
                IconColumn::make('has_shared_code')
                    ->label('Code')
                    ->boolean()
                    ->state(fn (LicenseProduct $record): bool => filled($record->shared_license_code))
                    ->trueIcon('heroicon-o-key')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->recordUrl(fn (LicenseProduct $record): string => LicenseProductResource::getUrl('edit', ['record' => $record]));
    }
}
