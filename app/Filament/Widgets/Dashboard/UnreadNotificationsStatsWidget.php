<?php

namespace App\Filament\Widgets\Dashboard;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UnreadNotificationsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 59;

    protected ?string $heading = 'Benachrichtigungen';

    protected function getStats(): array
    {
        $user = Auth::user();
        $unread = $user ? $user->unreadNotifications()->count() : 0;

        return [
            Stat::make('Ungelesen (In-App)', (string) $unread)
                ->color($unread > 0 ? 'warning' : 'success'),
        ];
    }
}
