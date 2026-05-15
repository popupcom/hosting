<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\NavigationGroups;
use App\Filament\Support\Schemas\LicenseAssignmentTableColumns;
use App\Models\ProjectLicenseAssignment;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
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

    protected static string|UnitEnum|null $navigationGroup = NavigationGroups::Leistungskatalog;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?int $navigationSort = 25;

    protected static ?string $slug = 'lizenznutzung';

    protected string $view = 'filament.pages.license-usage-overview';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProjectLicenseAssignment::query()
                    ->with(['project.client', 'licenseProduct'])
            )
            ->defaultSort('activated_at', 'desc')
            ->columns(LicenseAssignmentTableColumns::defaults(includeProject: true, includeProduct: true))
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(GermanLabels::licenseAssignmentStatuses()),
                SelectFilter::make('license_model')
                    ->label('Lizenzmodell')
                    ->options(GermanLabels::licenseSharingModels())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        if (blank($value)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'licenseProduct',
                            fn (Builder $q) => $q->where('license_model', $value),
                        );
                    }),
            ])
            ->recordUrl(function (ProjectLicenseAssignment $record): ?string {
                if ($record->project_id === null) {
                    return null;
                }

                return ProjectResource::getUrl('edit', ['record' => $record->project_id]);
            });
    }
}
