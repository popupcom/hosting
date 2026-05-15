<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Enums\LicenseAssignmentStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\LicenseProduct;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectLicensesRelationManager extends RelationManager
{
    protected static string $relationship = 'projectLicenses';

    protected static ?string $title = 'Lizenznutzung';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lizenz zuweisen')
                    ->description('Lizenzen werden nicht verrechnet — nur Kontingent und Nutzung erfassen.')
                    ->columns(2)
                    ->schema([
                        Select::make('license_product_id')
                            ->label('Lizenzprodukt')
                            ->relationship(
                                name: 'licenseProduct',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->activeCatalog()->orderBy('name'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(GermanLabels::licenseAssignmentStatuses())
                            ->default(LicenseAssignmentStatus::Active->value)
                            ->required()
                            ->native(false),
                        DatePicker::make('assigned_at')
                            ->label('Zugewiesen am')
                            ->native(false),
                        DatePicker::make('activated_at')
                            ->label('Aktiviert am')
                            ->native(false),
                        DatePicker::make('cancellation_effective_date')
                            ->label('Wirksam bis / Ende')
                            ->native(false),
                        DatePicker::make('cancelled_at')
                            ->label('Gekündigt am')
                            ->native(false),
                        Toggle::make('do_not_renew')->label('Nicht verlängern'),
                        Textarea::make('cancellation_reason')
                            ->label('Kündigungsgrund')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('licenseProduct'))
            ->columns([
                TextColumn::make('licenseProduct.name')
                    ->label('Produkt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('licenseProduct.freeLicensesCount')
                    ->label('Frei (Produkt)')
                    ->state(fn ($record): int => $record->licenseProduct?->freeLicensesCount() ?? 0),
                TextColumn::make('assigned_at')
                    ->label('Zugewiesen')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cancellation_effective_date')
                    ->label('Ende')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?LicenseAssignmentStatus $state): string => GermanLabels::licenseAssignmentStatus($state))
                    ->color(fn ($record): string => StatusBadge::licenseAssignment($record->status)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->before(function (CreateAction $action, array $data): void {
                        $productId = $data['license_product_id'] ?? null;
                        if (blank($productId)) {
                            return;
                        }
                        $product = LicenseProduct::query()->find($productId);
                        if ($product !== null && $product->isFullyUtilized()) {
                            Notification::make()
                                ->title('Kontingent ausgeschöpft')
                                ->body('Für „'.$product->name.'“ sind keine freien Lizenzen verfügbar.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
