<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Enums\BookingStatus;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')->label('Referenz')->searchable()->copyable(),
                TextColumn::make('hotel.name')->label('Hotel')->searchable()->sortable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?BookingStatus $state): string => $state?->label() ?? '–')
                    ->color(fn (?BookingStatus $state): string => match ($state) {
                        BookingStatus::Confirmed => 'success',
                        BookingStatus::PendingPayment => 'warning',
                        BookingStatus::Cancelled, BookingStatus::Expired => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('unit.name')->label('Unterkunft')->toggleable(),
                TextColumn::make('check_in')->label('Anreise')->date()->sortable(),
                TextColumn::make('nights')->label('Nächte')->alignCenter(),
                TextColumn::make('grand_total')->label('Gesamt')->money('EUR')->sortable(),
                IconColumn::make('insurance')->label('Vers.')->boolean()->toggleable(),
                TextColumn::make('created_at')->label('Erstellt')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')
                    ->options(fn (): array => collect(BookingStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()),
                SelectFilter::make('hotel')->label('Hotel')->relationship('hotel', 'name'),
            ]);
    }
}
