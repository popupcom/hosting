<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\DashboardPreference;
use App\Services\Dashboard\ProjectServiceDashboardQuery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class ProjectMarginLowTableWidget extends TableWidget
{
    protected static ?int $sort = 63;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Niedrige oder negative Marge je Projekt')
            ->paginated(false)
            ->emptyStateHeading('Keine Daten')
            ->records(fn (): Collection => $this->records())
            ->columns([
                TextColumn::make('project_name')
                    ->label('Projekt'),
                TextColumn::make('total_vk')
                    ->label('VK')
                    ->money('EUR'),
                TextColumn::make('total_ek')
                    ->label('EK')
                    ->money('EUR'),
                TextColumn::make('margin')
                    ->label('Marge')
                    ->money('EUR'),
            ]);
    }

    private function records(): Collection
    {
        $pref = DashboardPreference::forUser();

        return ProjectServiceDashboardQuery::lowestProjectMargins(8, $pref)
            ->mapWithKeys(function ($row): array {
                $id = (string) ($row->project_id ?? '0');

                return [
                    $id => [
                        '__key' => $id,
                        'project_name' => $row->project?->name ?? '—',
                        'total_vk' => (float) ($row->total_vk ?? 0),
                        'total_ek' => (float) ($row->total_ek ?? 0),
                        'margin' => (float) ($row->margin ?? 0),
                    ],
                ];
            });
    }
}
