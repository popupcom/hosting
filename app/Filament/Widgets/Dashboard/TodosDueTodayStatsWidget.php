<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\Reminder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodosDueTodayStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 22;

    protected ?string $heading = 'ToDos heute fällig';

    protected function getStats(): array
    {
        $count = Reminder::query()->dueToday()->count();

        return [
            Stat::make('Heute fällig', (string) $count)
                ->color($count > 0 ? 'warning' : 'gray'),
        ];
    }
}
