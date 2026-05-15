<?php

namespace App\Support;

use App\Enums\LicenseSharingModel;
use App\Models\LicenseProduct;
use App\Models\ProjectLicenseAssignment;
use Illuminate\Validation\ValidationException;

final class LicenseCodeRules
{
    public static function productUsesSharedCode(LicenseProduct $product): bool
    {
        return $product->license_model === LicenseSharingModel::Shared
            && ! $product->requires_individual_license_code;
    }

    public static function productRequiresAssignmentCode(LicenseProduct $product): bool
    {
        if ($product->license_model === LicenseSharingModel::Dedicated) {
            return true;
        }

        if ($product->license_model === LicenseSharingModel::SeatBased) {
            return true;
        }

        return (bool) $product->requires_individual_license_code;
    }

    public static function effectiveCode(ProjectLicenseAssignment $assignment): ?string
    {
        $product = $assignment->relationLoaded('licenseProduct')
            ? $assignment->licenseProduct
            : $assignment->licenseProduct()->first();

        if ($product === null) {
            return $assignment->license_code;
        }

        if (self::productUsesSharedCode($product)) {
            return $product->shared_license_code;
        }

        return $assignment->license_code;
    }

    public static function validateAssignment(ProjectLicenseAssignment $assignment): void
    {
        $product = $assignment->licenseProduct;

        if ($product === null && $assignment->license_product_id) {
            $product = LicenseProduct::query()->find($assignment->license_product_id);
        }

        if ($product === null) {
            return;
        }

        if (self::productUsesSharedCode($product)) {
            $assignment->license_code = null;

            return;
        }

        if (self::productRequiresAssignmentCode($product) && blank($assignment->license_code)) {
            throw ValidationException::withMessages([
                'license_code' => 'Für dedizierte Lizenzen ist ein individueller Lizenzcode pro Projekt erforderlich.',
            ]);
        }
    }

    public static function validateProduct(LicenseProduct $product): void
    {
        if (self::productUsesSharedCode($product) && blank($product->shared_license_code)) {
            throw ValidationException::withMessages([
                'shared_license_code' => 'Bei geteilten Lizenzen ist ein gemeinsamer Lizenzcode erforderlich.',
            ]);
        }
    }
}
