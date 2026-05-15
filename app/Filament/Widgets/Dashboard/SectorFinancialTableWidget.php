<?php

namespace App\Filament\Widgets\Dashboard;

use App\Enums\ServiceCatalogCategory;
use App\Filament\Support\GermanLabels;
use App\Models\DashboardPreference;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class SectorFinancialTableWidget extends TableWidget
{
    protected static ?int $sort = 50;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Einnahmen, Kosten und Marge nach Leistungskategorie')
            ->paginated(false)
            ->emptyStateHeading('Keine Daten')
            ->emptyStateDescription('Für die gewählten Filter liegen keine Projekt-Leistungen vor.')
            ->records(fn (): Collection => $this->sectorRecords())
            ->columns([
                TextColumn::make('label')
                    ->label('Kategorie'),
                TextColumn::make('revenue')
                    ->label('VK')
                    ->money('EUR'),
                TextColumn::make('cost')
                    ->label('EK')
                    ->money('EUR'),
                TextColumn::make('margin')
                    ->label('Marge')
                    ->money('EUR'),
            ]);
    }

    private function sectorRecords(): Collection
    {
        $pref = DashboardPreference::forUser();
        $rows = collect(ProjectServiceDashboardQuery::sectorBreakdown($pref))->keyBy('category');

        $ordered = [];
        foreach (ServiceCatalogCategory::cases() as $case) {
            $key = $case->value;
            $row = $rows->get($key);
            $ordered[] = [
                '__key' => $key,
                'label' => GermanLabels::serviceCatalogCategory($case),
                'revenue' => $row['revenue'] ?? 0.0,
                'cost' => $row['cost'] ?? 0.0,
                'margin' => $row['margin'] ?? 0.0,
            ];
        }

        return collect($ordered)->values();
    }
}
