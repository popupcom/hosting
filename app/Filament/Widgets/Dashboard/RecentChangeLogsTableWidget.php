<?php

namespace App\Filament\Widgets\Dashboard;

use App\Models\ChangeLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentChangeLogsTableWidget extends BaseWidget
{
    protected static ?int $sort = 58;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Letzte Änderungen';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ChangeLog::query()
                    ->with(['user', 'changeable'])
                    ->latest('created_at')
                    ->limit(50)
            )
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Zeit')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->label('Ereignis')
                    ->badge(),
                TextColumn::make('field_name')
                    ->label('Feld'),
                TextColumn::make('old_value')
                    ->label('Bisher')
                    ->limit(30),
                TextColumn::make('new_value')
                    ->label('Neu')
                    ->limit(30),
                TextColumn::make('user.name')
                    ->label('Von')
                    ->placeholder('System'),
            ]);
    }
}
