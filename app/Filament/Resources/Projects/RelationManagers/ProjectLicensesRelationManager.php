<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Filament\Support\Schemas\LicenseAssignmentFormSchema;
use App\Filament\Support\Schemas\LicenseAssignmentTableColumns;
use App\Models\LicenseProduct;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
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
            ->components(LicenseAssignmentFormSchema::components(
                fixedLicenseProductId: null,
                showProjectSelect: false,
                showLicenseProductSelect: true,
            ));
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('licenseProduct'))
            ->columns([
                ...LicenseAssignmentTableColumns::defaults(includeProject: false, includeProduct: true),
                TextColumn::make('licenseProduct.freeLicensesCount')
                    ->label('Frei (Produkt)')
                    ->state(fn ($record): int => $record->licenseProduct?->freeLicensesCount() ?? 0),
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
