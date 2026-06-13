<?php

namespace App\Filament\Widgets;

use App\Enums\BookingEventType;
use App\Models\BookingEvent;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Conversion funnel built from first-party booking_events. Demonstrates the
 * "trackable booking flow" value: the platform owns the funnel data regardless
 * of ad-blockers or third-party cookie loss.
 */
class BookingFunnelWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Conversion-Funnel (letzte 30 Tage)';

    protected function getStats(): array
    {
        $since = now()->subDays(30);

        $counts = BookingEvent::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('type, count(distinct session_id) as sessions')
            ->groupBy('type')
            ->pluck('sessions', 'type');

        $views = (int) ($counts[BookingEventType::PageView->value] ?? 0);
        $selects = (int) ($counts[BookingEventType::SelectUnit->value] ?? 0);
        $payments = (int) ($counts[BookingEventType::AddPaymentInfo->value] ?? 0);
        $purchases = (int) ($counts[BookingEventType::Purchase->value] ?? 0);

        $rate = fn (int $part, int $whole): string => $whole > 0
            ? number_format($part / $whole * 100, 1).' %'
            : '–';

        return [
            Stat::make('Sessions', (string) $views)
                ->description('Buchungsstrecke geöffnet'),
            Stat::make('Unterkunft gewählt', (string) $selects)
                ->description($rate($selects, $views).' der Sessions'),
            Stat::make('Zahlung gestartet', (string) $payments)
                ->description($rate($payments, $views).' der Sessions'),
            Stat::make('Buchungen', (string) $purchases)
                ->description('Conversion-Rate '.$rate($purchases, $views))
                ->color('success'),
        ];
    }
}
