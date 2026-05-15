<?php

namespace App\Services\Imports;

use App\Enums\ClientStatus;
use App\Enums\MaintenanceType;
use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\ServiceCatalogBillingInterval;
use App\Enums\ServiceCatalogCategory;
use App\Enums\ServiceCatalogUnit;
use App\Models\Client;
use App\Models\MaintenanceHistory;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\ServiceCatalogItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

final class SupportPackageExcelImporter
{
    public const SHEET_NAME = 'Supportpakete Import';

    /**
     * @return list<array<string, mixed>>
     */
    public function peek(string $path, int $limit = 25): array
    {
        $rows = $this->readAssocRows($path);

        return array_slice($rows, 0, $limit);
    }

    /**
     * Lightweight validation for preview (Pflichtfelder + support_package für Import).
     *
     * @param  array<string, string|null>  $row
     * @return list<string>
     */
    public function rowPreviewErrors(array $row): array
    {
        $errors = [];
        if (trim((string) ($row['customer_name'] ?? '')) === '') {
            $errors[] = 'customer_name fehlt';
        }
        if (trim((string) ($row['website'] ?? '')) === '') {
            $errors[] = 'website fehlt';
        }
        if (trim((string) ($row['support_package'] ?? '')) === '') {
            $errors[] = 'support_package fehlt (Import bricht sonst ab)';
        }

        return $errors;
    }

    public function import(string $path): SupportPackageExcelImportResult
    {
        $result = new SupportPackageExcelImportResult;
        $rows = $this->readAssocRows($path);

        foreach ($rows as $index => $row) {
            $excelRow = $index + 2;

            try {
                $this->importRow($row, $result);
            } catch (Throwable $e) {
                $result->addError([
                    'row' => $excelRow,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }

    /**
     * @return list<array<string, string|null>>
     */
    private function readAssocRows(string $path): array
    {
        if (! is_readable($path)) {
            throw new \InvalidArgumentException('Die Datei ist nicht lesbar.');
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName(self::SHEET_NAME) ?? $spreadsheet->getSheet(0);
        $matrix = $sheet->toArray(null, true, true, false);
        if ($matrix === [] || $matrix[0] === []) {
            return [];
        }

        $headerRow = array_shift($matrix);
        $headers = [];
        foreach ($headerRow as $col => $cell) {
            $key = $this->normalizeHeaderKey((string) $cell);
            if ($key !== '') {
                $headers[$col] = $key;
            }
        }

        $out = [];
        foreach ($matrix as $row) {
            if (! is_array($row)) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $col => $key) {
                $assoc[$key] = isset($row[$col]) ? $this->cellToString($row[$col]) : null;
            }
            if ($this->isRowEmpty($assoc)) {
                continue;
            }
            $out[] = $assoc;
        }

        return $out;
    }

    private function normalizeHeaderKey(string $cell): string
    {
        $s = Str::lower(trim((string) $cell));
        $s = str_replace([' ', '-'], '_', $s);

        return $s;
    }

    private function cellToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_string($value)) {
            $t = trim($value);

            return $t === '' ? null : $t;
        }
        if (is_numeric($value)) {
            return (string) $value;
        }

        return trim((string) $value) ?: null;
    }

    /**
     * @param  array<string, string|null>  $assoc
     */
    private function isRowEmpty(array $assoc): bool
    {
        foreach ($assoc as $v) {
            if ($v !== null && trim((string) $v) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function importRow(array $row, SupportPackageExcelImportResult $result): void
    {
        $customerName = trim((string) ($row['customer_name'] ?? ''));
        $websiteRaw = trim((string) ($row['website'] ?? ''));

        if ($customerName === '' || $websiteRaw === '') {
            throw new \InvalidArgumentException('Pflichtfelder fehlen: customer_name und website.');
        }

        DB::transaction(function () use ($row, $result, $customerName, $websiteRaw): void {
            $client = Client::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($customerName)])
                ->first();

            if ($client === null) {
                $client = new Client;
                $client->name = $customerName;
                $client->slug = $this->uniqueClientSlug($customerName);
                $client->status = ClientStatus::Active;
                $client->company = '';
                $client->save();
                $result->clientsCreated++;
            } else {
                $dirty = false;
                if ($client->status !== ClientStatus::Active) {
                    $client->status = ClientStatus::Active;
                    $dirty = true;
                }
                if ($dirty) {
                    $client->save();
                    $result->clientsUpdated++;
                }
            }

            $url = $this->normalizeWebsiteUrl($websiteRaw);
            $projectName = $this->deriveProjectName($url);

            $project = Project::query()
                ->where('client_id', $client->id)
                ->where('url', $url)
                ->first();

            if ($project === null) {
                $project = new Project;
                $project->client_id = $client->id;
                $project->name = $projectName;
                $project->url = $url;
                $project->status = ProjectStatus::Active;
                $project->maintenance_contract = true;
                $project->save();
                $result->projectsCreated++;
            } else {
                $dirty = false;
                if ($project->name !== $projectName) {
                    $project->name = $projectName;
                    $dirty = true;
                }
                if ($project->status !== ProjectStatus::Active) {
                    $project->status = ProjectStatus::Active;
                    $dirty = true;
                }
                if ($project->maintenance_contract !== true) {
                    $project->maintenance_contract = true;
                    $dirty = true;
                }
                if ($dirty) {
                    $project->save();
                    $result->projectsUpdated++;
                }
            }

            $packageLabel = trim((string) ($row['support_package'] ?? ''));
            if ($packageLabel === '') {
                throw new \InvalidArgumentException('support_package fehlt.');
            }

            $catalogItem = $this->resolveSupportCatalogItem($packageLabel, $result);

            $billing = $this->parseBillingInterval((string) ($row['billing_period'] ?? ''));
            $vk = $this->resolveSalesPrice($row, $billing);
            $mocoSync = $this->mapMocoStatus((string) ($row['moco_status'] ?? ''));
            $invoiceRef = $this->truncate($row['invoice_reference'] ?? null, 255);
            $priceChange = $this->parseDateFlexible($row['price_change_from'] ?? null);

            $notesParts = array_filter([
                isset($row['billing_period']) && trim((string) $row['billing_period']) !== ''
                    ? 'Verrechnungszeitraum (Excel): '.trim((string) $row['billing_period'])
                    : null,
                isset($row['comment']) && trim((string) $row['comment']) !== ''
                    ? 'Kommentar: '.trim((string) $row['comment'])
                    : null,
                isset($row['open_todos']) && trim((string) $row['open_todos']) !== ''
                    ? 'Offene Aufgaben: '.trim((string) $row['open_todos'])
                    : null,
            ]);
            $serviceNotes = $notesParts !== [] ? implode("\n\n", $notesParts) : null;

            $ps = ProjectService::query()->firstOrNew([
                'project_id' => $project->id,
                'service_catalog_item_id' => $catalogItem->id,
            ]);

            $wasPsNew = ! $ps->exists;

            $ps->quantity = $ps->quantity ?: 1;
            $ps->status = ProjectServiceStatus::Active;
            $ps->moco_sync_status = $mocoSync;
            $ps->custom_billing_interval = $billing;
            if ($vk !== null) {
                $ps->custom_sales_price = $vk;
            }
            $ps->moco_invoice_reference = $invoiceRef;
            $ps->price_change_effective_from = $priceChange;
            $ps->notes = $serviceNotes;
            $ps->save();

            if ($wasPsNew) {
                $result->projectServicesCreated++;
            } else {
                $result->projectServicesUpdated++;
            }

            $updateStatus = trim((string) ($row['update_status'] ?? ''));
            if ($updateStatus !== '') {
                $marker = $this->importRowMarker($customerName, $websiteRaw, $packageLabel);
                $mh = MaintenanceHistory::query()
                    ->where('project_id', $project->id)
                    ->where('notes', 'like', $marker.'%')
                    ->first();

                $mhNotes = $this->buildMaintenanceNotes($row, $marker);

                $hasErrors = mb_stripos($updateStatus, 'fehler') !== false
                    || mb_stripos((string) ($row['moco_status'] ?? ''), 'fehler') !== false;

                if ($mh === null) {
                    $mh = new MaintenanceHistory;
                    $mh->project_id = $project->id;
                    $mh->maintenance_type = MaintenanceType::SupportPackageExcelSnapshot;
                    $mh->performed_by = 'Excel-Import';
                    $mh->performed_on = $priceChange ?? CarbonImmutable::now()->toDateString();
                    $mh->result = $updateStatus;
                    $mh->has_errors = $hasErrors;
                    $mh->notes = $mhNotes;
                    $mh->save();
                    $result->maintenanceHistoriesCreated++;
                } else {
                    $mh->maintenance_type = MaintenanceType::SupportPackageExcelSnapshot;
                    $mh->performed_by = 'Excel-Import';
                    $mh->performed_on = $priceChange ?? $mh->performed_on;
                    $mh->result = $updateStatus;
                    $mh->has_errors = $hasErrors;
                    $mh->notes = $mhNotes;
                    $mh->save();
                    $result->maintenanceHistoriesUpdated++;
                }
            }
        });
    }

    private function uniqueClientSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'kundin';
        $slug = $base;
        $i = 1;
        while (Client::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function normalizeWebsiteUrl(string $raw): string
    {
        $u = trim($raw);
        if ($u === '') {
            return '';
        }
        if (! preg_match('#^https?://#i', $u)) {
            $u = 'https://'.$u;
        }

        return rtrim($u, '/');
    }

    private function deriveProjectName(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return Str::limit($host !== null && $host !== '' ? $host : $url, 255);
    }

    private function resolveSupportCatalogItem(string $label, SupportPackageExcelImportResult $result): ServiceCatalogItem
    {
        $slug = Str::slug($label);
        $slug = Str::limit($slug !== '' ? $slug : 'supportpaket', 120, '');

        $item = ServiceCatalogItem::query()
            ->where('category', ServiceCatalogCategory::SupportPackage)
            ->where(function ($q) use ($label, $slug): void {
                $q->whereRaw('LOWER(name) = ?', [mb_strtolower($label)])
                    ->orWhere('slug', $slug);
            })
            ->first();

        if ($item !== null) {
            return $item;
        }

        $uniqueSlug = $slug;
        $n = 1;
        while (ServiceCatalogItem::query()->where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $slug.'-'.$n;
            $n++;
        }

        $item = ServiceCatalogItem::query()->create([
            'name' => $label,
            'slug' => $uniqueSlug,
            'category' => ServiceCatalogCategory::SupportPackage,
            'description' => 'Automatisch aus Excel-Import angelegt.',
            'unit' => ServiceCatalogUnit::Month,
            'default_quantity' => 1,
            'billing_interval' => ServiceCatalogBillingInterval::Monthly,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $result->catalogItemsCreated++;

        return $item;
    }

    private function parseBillingInterval(string $raw): ServiceCatalogBillingInterval
    {
        $s = mb_strtolower(trim($raw));
        if ($s === '') {
            return ServiceCatalogBillingInterval::Monthly;
        }
        if (str_contains($s, 'jahr') || str_contains($s, 'year') || str_contains($s, 'jähr')) {
            return ServiceCatalogBillingInterval::Yearly;
        }

        return ServiceCatalogBillingInterval::Monthly;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function resolveSalesPrice(array $row, ServiceCatalogBillingInterval $billing): ?string
    {
        $month = $this->parseDecimal($row['price_month_2026'] ?? null);
        $year = $this->parseDecimal($row['price_year_2026'] ?? null);

        if ($billing === ServiceCatalogBillingInterval::Yearly) {
            return $year ?? $month;
        }

        return $month ?? $year;
    }

    private function parseDecimal(mixed $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        if (is_float($raw) || is_int($raw)) {
            return number_format((float) $raw, 2, '.', '');
        }
        if (trim((string) $raw) === '') {
            return null;
        }
        $normalized = str_replace(['€', ' ', "\xc2\xa0"], '', (string) $raw);
        $normalized = str_replace(',', '.', $normalized);
        if (! is_numeric($normalized)) {
            return null;
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    private function mapMocoStatus(string $raw): ProjectServiceMocoSyncStatus
    {
        $s = mb_strtolower(trim($raw));
        if ($s === '') {
            return ProjectServiceMocoSyncStatus::NotSynced;
        }
        if (str_contains($s, 'erledigt') || str_contains($s, 'verrechnet')) {
            return ProjectServiceMocoSyncStatus::Synced;
        }
        if (str_contains($s, 'fehler')) {
            return ProjectServiceMocoSyncStatus::Error;
        }
        if (str_contains($s, 'offen')) {
            return ProjectServiceMocoSyncStatus::Ready;
        }

        return ProjectServiceMocoSyncStatus::NotSynced;
    }

    private function parseDateFlexible(mixed $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        if (is_float($raw) || is_int($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }
        if (trim((string) $raw) === '') {
            return null;
        }
        if (is_numeric($raw)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $raw)->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }
        $t = trim((string) $raw);
        foreach (['Y-m-d', 'd.m.Y', 'd/m/Y'] as $fmt) {
            try {
                $d = CarbonImmutable::createFromFormat($fmt, $t);

                return $d->toDateString();
            } catch (Throwable) {
                continue;
            }
        }

        $ts = strtotime($t);

        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function importRowMarker(string $customer, string $website, string $package): string
    {
        $hash = hash('sha256', mb_strtolower(trim($customer)).'|'.mb_strtolower(trim($website)).'|'.mb_strtolower(trim($package)));

        return '[excel-import-row:'.$hash.']';
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function buildMaintenanceNotes(array $row, string $marker): string
    {
        $parts = [$marker];
        if (filled($row['open_todos'] ?? null)) {
            $parts[] = 'Offene Aufgaben: '.trim((string) $row['open_todos']);
        }
        if (filled($row['comment'] ?? null)) {
            $parts[] = 'Kommentar: '.trim((string) $row['comment']);
        }
        if (filled($row['billing_period'] ?? null)) {
            $parts[] = 'Verrechnungszeitraum: '.trim((string) $row['billing_period']);
        }

        return implode("\n\n", $parts);
    }

    private function truncate(?string $v, int $max): ?string
    {
        if ($v === null) {
            return null;
        }

        return Str::limit(trim($v), $max, '');
    }
}
