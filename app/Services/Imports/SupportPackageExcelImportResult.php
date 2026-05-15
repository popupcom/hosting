<?php

namespace App\Services\Imports;

final class SupportPackageExcelImportResult
{
    public int $clientsCreated = 0;

    public int $clientsUpdated = 0;

    public int $projectsCreated = 0;

    public int $projectsUpdated = 0;

    public int $projectServicesCreated = 0;

    public int $projectServicesUpdated = 0;

    public int $maintenanceHistoriesCreated = 0;

    public int $maintenanceHistoriesUpdated = 0;

    public int $catalogItemsCreated = 0;

    public int $errorCount = 0;

    /** @var list<array{row: int, message: string}> */
    public array $errors = [];

    /**
     * @param  array{row: int, message: string}  $error
     */
    public function addError(array $error): void
    {
        $this->errors[] = $error;
        $this->errorCount++;
    }
}
