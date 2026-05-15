<?php

namespace App\Filament\Widgets\Dashboard;

use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class TopProjectServicesByRevenueTableWidget extends TableWidget
{
    protected static ?int $sort = 59;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top-Leistungen nach Umsatz (aktive Zuweisungen, VK × Menge)')
            ->paginated(false)
            ->emptyStateHeading('Keine Daten')
            ->emptyStateDescription('Keine aktiven Projekt-Leistungen mit VK.')
            ->records(fn (): Collection => ProjectServiceDashboardQuery::topServicesByRevenue(15))
            ->columns([
                TextColumn::make('name')
                    ->label('Leistung'),
                TextColumn::make('revenue')
                    ->label('Umsatz')
                    ->money('EUR'),
            ]);
    }
}
