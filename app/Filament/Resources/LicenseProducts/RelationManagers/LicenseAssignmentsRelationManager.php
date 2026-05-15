<?php

namespace App\Filament\Resources\LicenseProducts\RelationManagers;

use App\Filament\Support\Schemas\LicenseAssignmentFormSchema;
use App\Filament\Support\Schemas\LicenseAssignmentTableColumns;
use App\Models\LicenseProduct;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LicenseAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Projekt-Zuweisungen';

    public function form(Schema $schema): Schema
    {
        /** @var LicenseProduct $product */
        $product = $this->getOwnerRecord();

        return $schema
            ->components(LicenseAssignmentFormSchema::components(
                fixedLicenseProductId: $product->getKey(),
                showProjectSelect: true,
                showLicenseProductSelect: false,
            ));
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['project.client', 'licenseProduct']))
            ->columns(LicenseAssignmentTableColumns::defaults(includeProject: true, includeProduct: false))
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
