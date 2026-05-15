<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\Reminder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverdueTodosStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 21;

    protected ?string $heading = 'Überfällige ToDos';

    protected function getStats(): array
    {
        $count = Reminder::query()->overdue()->count();

        return [
            Stat::make('Überfällige ToDos', (string) $count)
                ->color($count > 0 ? 'danger' : 'success'),
        ];
    }
}
