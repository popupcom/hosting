<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Models\Booking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'reference';

    protected static ?string $modelLabel = 'Buchung';

    protected static ?string $pluralModelLabel = 'Buchungen';

    protected static string|UnitEnum|null $navigationGroup = 'Buchungs-SaaS';

    protected static ?int $navigationSort = 20;

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
        ];
    }
}
