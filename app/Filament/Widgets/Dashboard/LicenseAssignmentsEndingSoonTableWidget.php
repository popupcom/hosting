<?php

namespace App\Filament\Widgets\Dashboard;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Support\Schemas\LicenseAssignmentTableColumns;
use App\Models\ProjectLicenseAssignment;
use App\Services\Dashboard\LicenseUsageDashboardQuery;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LicenseAssignmentsEndingSoonTableWidget extends BaseWidget
{
    protected static ?int $sort = 60;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Bald endende Lizenznutzungen (60 Tage)';

    public function table(Table $table): Table
    {
        return $table
            ->query(LicenseUsageDashboardQuery::assignmentsEndingSoonQuery(60))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns(LicenseAssignmentTableColumns::defaults(includeProject: true, includeProduct: true))
            ->recordUrl(fn (ProjectLicenseAssignment $record): ?string => $record->project_id
                ? ProjectResource::getUrl('edit', ['record' => $record->project_id])
                : null);
    }
}
