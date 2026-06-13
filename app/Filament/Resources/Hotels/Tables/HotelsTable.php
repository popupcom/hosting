<?php

namespace App\Filament\Resources\Hotels\Tables;

use App\Enums\HotelStatus;
use App\Enums\SubscriptionPlan;
use App\Models\Hotel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HotelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Hotel')->searchable()->sortable()
                    ->description(fn (Hotel $r): ?string => $r->location),
                TextColumn::make('slug')->label('Slug')->searchable()->copyable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?HotelStatus $state): string => $state?->label() ?? '–')
                    ->color(fn (?HotelStatus $state): string => match ($state) {
                        HotelStatus::Active => 'success',
                        HotelStatus::Onboarding => 'info',
                        HotelStatus::Paused => 'warning',
                        HotelStatus::Churned => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subscription_plan')->label('Tarif')->badge()
                    ->formatStateUsing(fn (?SubscriptionPlan $state): string => $state?->label() ?? '–'),
                TextColumn::make('units_count')->label('Unterkünfte')->counts('units')->alignCenter(),
                TextColumn::make('bookings_count')->label('Buchungen')->counts('bookings')->alignCenter(),
                TextColumn::make('client.name')->label('Kund:in')->toggleable(),
                TextColumn::make('created_at')->label('Erstellt')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')
                    ->options(fn (): array => collect(HotelStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
