<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\LicenseAssignmentStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\ProjectLicenseAssignment;
use App\Services\Dashboard\LicenseUsageDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LicenseAssignmentsEndingSoonTableWidget extends BaseWidget
{
    protected static ?int $sort = 57;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Lizenz-Zuweisungen: enden in 60 Tagen';

    public function table(Table $table): Table
    {
        return $table
            ->query(LicenseUsageDashboardQuery::assignmentsEndingSoonQuery(60))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('licenseProduct.name')
                    ->label('Produkt'),
                TextColumn::make('project.name')
                    ->label('Projekt'),
                TextColumn::make('project.client.name')
                    ->label('Kund:in'),
                TextColumn::make('cancellation_effective_date')
                    ->label('Ende')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?LicenseAssignmentStatus $state): string => GermanLabels::licenseAssignmentStatus($state))
                    ->color(fn (ProjectLicenseAssignment $record): string => StatusBadge::licenseAssignment($record->status)),
            ]);
    }
}
