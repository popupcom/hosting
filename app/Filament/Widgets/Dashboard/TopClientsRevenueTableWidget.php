<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\DashboardPreference;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class TopClientsRevenueTableWidget extends TableWidget
{
    protected static ?int $sort = 60;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top Einnahmen je Kund:in')
            ->paginated(false)
            ->emptyStateHeading('Keine Daten')
            ->emptyStateDescription('Keine Projekt-Leistungen für die aktuellen Filter.')
            ->records(fn (): Collection => $this->records())
            ->columns([
                TextColumn::make('client_name')
                    ->label('Kund:in'),
                TextColumn::make('total_vk')
                    ->label('Summe VK')
                    ->money('EUR'),
            ]);
    }

    private function records(): Collection
    {
        $pref = DashboardPreference::forUser();

        return ProjectServiceDashboardQuery::topClientsBySelling(8, $pref)
            ->mapWithKeys(function ($row): array {
                $id = (string) ($row->client_id ?? '0');

                return [
                    $id => [
                        '__key' => $id,
                        'client_name' => $row->client_name ?? '—',
                        'total_vk' => (float) ($row->total_vk ?? 0),
                    ],
                ];
            });
    }
}
