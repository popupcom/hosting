<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Support\GermanLabels;
use App\Models\Reminder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodosByGroupStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 23;

    protected ?string $heading = 'ToDos je Gruppe';

    protected function getStats(): array
    {
        $counts = Reminder::query()
            ->open()
            ->selectRaw('remindable_type, count(*) as aggregate')
            ->groupBy('remindable_type')
            ->pluck('aggregate', 'remindable_type');

        if ($counts->isEmpty()) {
            return [
                Stat::make('Keine offenen ToDos', '0')
                    ->color('success'),
            ];
        }

        return $counts
            ->map(fn (int|string $count, string $type): Stat => Stat::make(
                GermanLabels::todoRemindableType($type),
                (string) $count,
            )->color((int) $count > 0 ? 'warning' : 'gray'))
            ->values()
            ->all();
    }
}
