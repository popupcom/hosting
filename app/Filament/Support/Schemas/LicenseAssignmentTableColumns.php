<?php

namespace App\Filament\Support\Schemas;

use App\Enums\LicenseAssignmentStatus;
use App\Enums\LicenseSharingModel;
use App\Filament\Support\GermanLabels;
use App\Filament\Support\StatusBadge;
use App\Models\ProjectLicenseAssignment;
use Filament\Tables\Columns\TextColumn;

final class LicenseAssignmentTableColumns
{
    /**
     * @return array<int, TextColumn>
     */
    public static function defaults(bool $includeProject = true, bool $includeProduct = false): array
    {
        $columns = [];

        if ($includeProject) {
            $columns[] = TextColumn::make('project.name')
                ->label('Projekt')
                ->searchable()
                ->sortable();
            $columns[] = TextColumn::make('project.client.name')
                ->label('Kund:in')
                ->toggleable();
        }

        if ($includeProduct) {
            $columns[] = TextColumn::make('licenseProduct.name')
                ->label('Lizenzprodukt')
                ->searchable()
                ->sortable();
            $columns[] = TextColumn::make('licenseProduct.license_model')
                ->label('Modell')
                ->badge()
                ->formatStateUsing(fn (?LicenseSharingModel $state): string => GermanLabels::licenseSharingModel($state));
        }

        $columns[] = TextColumn::make('effective_license_code')
            ->label('Lizenzcode')
            ->state(fn (ProjectLicenseAssignment $record): string => $record->effectiveLicenseCode() ?? '—')
            ->limit(24)
            ->tooltip(fn (ProjectLicenseAssignment $record): ?string => $record->effectiveLicenseCode())
            ->toggleable();

        $columns[] = TextColumn::make('activated_at')
            ->label('Aktiviert am')
            ->dateTime()
            ->sortable();

        $columns[] = TextColumn::make('cancelled_at')
            ->label('Gekündigt am')
            ->dateTime()
            ->sortable()
            ->toggleable();

        $columns[] = TextColumn::make('cancellation_effective_date')
            ->label('Ende')
            ->date()
            ->sortable();

        $columns[] = TextColumn::make('status')
            ->label('Status')
            ->badge()
            ->formatStateUsing(fn (?LicenseAssignmentStatus $state): string => GermanLabels::licenseAssignmentStatus($state))
            ->color(fn (ProjectLicenseAssignment $record): string => StatusBadge::licenseAssignment($record->status));

        return $columns;
    }
}
