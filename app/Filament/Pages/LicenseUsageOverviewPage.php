<?php

namespace App\Filament\Pages;

use App\Enums\LicenseProductStatus;
use App\Filament\Resources\LicenseProducts\LicenseProductResource;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\LicenseProduct;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LicenseUsageOverviewPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Lizenznutzung';

    protected static ?string $navigationLabel = 'Lizenznutzung';

    protected static string|UnitEnum|null $navigationGroup = 'Lizenzen & Support';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?int $navigationSort = 47;

    protected static ?string $slug = 'lizenznutzung';

    protected string $view = 'filament.pages.license-usage-overview';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LicenseProduct::query()
                    ->withCount([
                        'assignments as used_count' => fn (Builder $q) => $q->countsAsUsed(),
                    ])
            )
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Produkt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('provider')
                    ->label('Anbieter')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->label('Kategorie')
                    ->toggleable(),
                TextColumn::make('total_available_licenses')
                    ->label('Kontingent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Belegt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('free_licenses')
                    ->label('Frei')
                    ->state(fn (LicenseProduct $record): int => $record->freeLicensesCount()),
                TextColumn::make('utilization')
                    ->label('Auslastung')
                    ->state(fn (LicenseProduct $record): string => ($p = $record->utilizationPercent()) !== null ? $p.' %' : '—')
                    ->color(fn (LicenseProduct $record): string => $record->isFullyUtilized() ? 'danger' : 'success'),
                IconColumn::make('quota_warning')
                    ->label('Voll')
                    ->boolean()
                    ->state(fn (LicenseProduct $record): bool => $record->isFullyUtilized())
                    ->trueIcon(Heroicon::OutlinedExclamationTriangle)
                    ->falseIcon(Heroicon::OutlinedCheckCircle)
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?LicenseProductStatus $state): string => GermanLabels::licenseProductStatus($state))
                    ->color(fn (LicenseProduct $record): string => StatusBadge::licenseProduct($record->status)),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::licenseProductStatuses()),
            ])
            ->recordUrl(fn (LicenseProduct $record): string => LicenseProductResource::getUrl('edit', ['record' => $record]));
    }
}
