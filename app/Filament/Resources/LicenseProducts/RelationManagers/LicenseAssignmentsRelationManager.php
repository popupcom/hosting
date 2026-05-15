<?php

namespace App\Filament\Resources\LicenseProducts\RelationManagers;

use App\Enums\LicenseAssignmentStatus;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
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

class LicenseAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Zuweisungen';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zuweisung')
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->label('Projekt')
                            ->relationship('project', 'name')
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['project.client']))
            ->columns([
                TextColumn::make('project.name')
                    ->label('Projekt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.client.name')
                    ->label('Kund:in')
                    ->toggleable(),
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
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['license_product_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    })
                    ->before(function (CreateAction $action): void {
                        $product = $this->getOwnerRecord();
                        if ($product->isFullyUtilized()) {
                            Notification::make()
                                ->title('Kontingent ausgeschöpft')
                                ->body('Für dieses Lizenzprodukt sind keine freien Lizenzen mehr verfügbar.')
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
