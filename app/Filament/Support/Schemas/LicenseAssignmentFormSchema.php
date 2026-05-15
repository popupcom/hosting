<?php

namespace App\Filament\Support\Schemas;

use App\Enums\LicenseAssignmentStatus;
use App\Filament\Support\GermanLabels;
use App\Models\LicenseProduct;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

final class LicenseAssignmentFormSchema
{
    /**
     * @return array<int, Component>
     */
    public static function components(
        ?int $fixedLicenseProductId = null,
        bool $showProjectSelect = true,
        bool $showLicenseProductSelect = false,
    ): array {
        $resolveProduct = function (Get $get) use ($fixedLicenseProductId): ?LicenseProduct {
            $productId = $fixedLicenseProductId ?? $get('license_product_id');

            if (blank($productId)) {
                return null;
            }

            return LicenseProduct::query()->find($productId);
        };

        $schema = [];

        if ($showLicenseProductSelect) {
            $schema[] = Select::make('license_product_id')
                ->label('Lizenzprodukt')
                ->relationship(
                    name: 'licenseProduct',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query->activeCatalog()->orderBy('name'),
                )
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->native(false);
        }

        if ($showProjectSelect) {
            $schema[] = Select::make('project_id')
                ->label('Projekt')
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->required();
        }

        $schema[] = Placeholder::make('shared_license_code_display')
            ->label('Gemeinsamer Lizenzcode')
            ->content(function (Get $get) use ($resolveProduct): string {
                $product = $resolveProduct($get);
                if ($product === null) {
                    return '—';
                }

                if (! $product->usesSharedLicenseCode()) {
                    return '—';
                }

                return filled($product->shared_license_code)
                    ? $product->shared_license_code
                    : 'Noch kein gemeinsamer Code hinterlegt';
            })
            ->visible(fn (Get $get): bool => $resolveProduct($get)?->usesSharedLicenseCode() ?? false)
            ->columnSpanFull();

        $schema[] = TextInput::make('license_code')
            ->label('Individueller Lizenzcode')
            ->maxLength(2048)
            ->password()
            ->revealable()
            ->required(fn (Get $get): bool => $resolveProduct($get)?->requiresAssignmentLicenseCode() ?? false)
            ->visible(fn (Get $get): bool => $resolveProduct($get)?->requiresAssignmentLicenseCode() ?? false)
            ->helperText('Pro Projekt eigener Lizenzschlüssel (dediziert).')
            ->columnSpanFull();

        return [
            Section::make('Lizenznutzung')
                ->description('Nur Kontingent und Nutzung — keine Verrechnung über Lizenzen.')
                ->columns(2)
                ->schema([
                    ...$schema,
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
        ];
    }
}
