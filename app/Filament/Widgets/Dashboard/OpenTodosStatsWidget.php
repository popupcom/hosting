<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\Reminder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OpenTodosStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 20;

    protected ?string $heading = 'Offene ToDos';

    protected function getStats(): array
    {
        $count = Reminder::query()->open()->count();

        return [
            Stat::make('Offene ToDos', (string) $count)
                ->color($count > 0 ? 'warning' : 'success'),
        ];
    }
}
