<?php

namespace Tests\Unit;

use App\Enums\LicenseAssignmentStatus;
use App\Enums\LicenseProductStatus;
use App\Enums\LicenseSharingModel;
use App\Models\Client;
use App\Models\LicenseProduct;
use App\Models\Project;
use App\Models\ProjectLicenseAssignment;
use App\Support\LicenseCodeRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LicenseCodeRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_product_uses_central_license_code(): void
    {
        $product = LicenseProduct::query()->create([
            'name' => 'AIO SEO',
            'license_model' => LicenseSharingModel::Shared,
            'shared_license_code' => 'SHARED-KEY-123',
            'total_available_licenses' => 10,
            'status' => LicenseProductStatus::Active,
        ]);

        $assignment = $this->createAssignment($product, licenseCode: 'should-be-cleared');
        $assignment->refresh();

        $this->assertTrue($product->usesSharedLicenseCode());
        $this->assertNull($assignment->license_code);
        $this->assertSame('SHARED-KEY-123', LicenseCodeRules::effectiveCode($assignment));
    }

    public function test_dedicated_product_requires_assignment_license_code(): void
    {
        $product = LicenseProduct::query()->create([
            'name' => 'Datenschutz',
            'license_model' => LicenseSharingModel::Dedicated,
            'total_available_licenses' => 5,
            'status' => LicenseProductStatus::Active,
        ]);

        $this->expectException(ValidationException::class);

        $this->createAssignment($product, licenseCode: null);
    }

    public function test_dedicated_assignment_stores_individual_code(): void
    {
        $product = LicenseProduct::query()->create([
            'name' => 'Theme',
            'license_model' => LicenseSharingModel::Dedicated,
            'total_available_licenses' => 5,
            'status' => LicenseProductStatus::Active,
        ]);

        $assignment = $this->createAssignment($product, licenseCode: 'PROJECT-KEY-99');

        $this->assertTrue($product->requiresAssignmentLicenseCode());
        $this->assertSame('PROJECT-KEY-99', LicenseCodeRules::effectiveCode($assignment));
    }

    public function test_shared_product_without_code_fails_validation(): void
    {
        $this->expectException(ValidationException::class);

        LicenseProduct::query()->create([
            'name' => 'Polylang',
            'license_model' => LicenseSharingModel::Shared,
            'shared_license_code' => null,
            'total_available_licenses' => 3,
            'status' => LicenseProductStatus::Active,
        ]);
    }

    private function createAssignment(LicenseProduct $product, ?string $licenseCode): ProjectLicenseAssignment
    {
        $client = Client::query()->create([
            'name' => 'Test Kundin',
            'slug' => 'test-kundin',
            'status' => 'active',
        ]);

        $project = Project::query()->create([
            'client_id' => $client->id,
            'name' => 'Test Projekt',
            'url' => 'https://example.test',
            'status' => 'active',
        ]);

        return ProjectLicenseAssignment::query()->create([
            'license_product_id' => $product->id,
            'project_id' => $project->id,
            'client_id' => $client->id,
            'license_code' => $licenseCode,
            'status' => LicenseAssignmentStatus::Active,
        ]);
    }
}
