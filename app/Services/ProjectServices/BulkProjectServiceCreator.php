<?php

namespace App\Services\ProjectServices;

use App\Enums\ProjectServiceMocoSyncStatus;
use App\Enums\ProjectServiceStatus;
use App\Models\Project;
use App\Models\ProjectService;
use App\Models\ServiceCatalogItem;
use Illuminate\Support\Collection;

final class BulkProjectServiceCreator
{
    /**
     * @param  list<int>  $catalogItemIds
     * @return array{created: int, skipped: int}
     */
    public static function createForProject(Project $project, array $catalogItemIds): array
    {
        $catalogItemIds = array_values(array_unique(array_filter($catalogItemIds)));

        if ($catalogItemIds === []) {
            return ['created' => 0, 'skipped' => 0];
        }

        $existingIds = ProjectService::query()
            ->where('project_id', $project->getKey())
            ->whereIn('service_catalog_item_id', $catalogItemIds)
            ->whereIn('status', [
                ProjectServiceStatus::Active->value,
                ProjectServiceStatus::PendingCancellation->value,
                ProjectServiceStatus::Paused->value,
            ])
            ->pluck('service_catalog_item_id')
            ->all();

        $items = ServiceCatalogItem::query()
            ->whereIn('id', $catalogItemIds)
            ->get()
            ->keyBy('id');

        $created = 0;
        $skipped = 0;

        foreach ($catalogItemIds as $catalogItemId) {
            if (in_array($catalogItemId, $existingIds, true)) {
                $skipped++;

                continue;
            }

            /** @var ServiceCatalogItem|null $item */
            $item = $items->get($catalogItemId);
            if ($item === null) {
                $skipped++;

                continue;
            }

            $service = new ProjectService([
                'project_id' => $project->getKey(),
                'service_catalog_item_id' => $item->getKey(),
                'quantity' => $item->default_quantity ?? 1,
                'status' => ProjectServiceStatus::Active,
                'moco_sync_status' => ProjectServiceMocoSyncStatus::NotSynced,
                'renews_automatically' => true,
                'do_not_renew' => false,
            ]);

            ProjectServiceSnapshotter::applyCatalogSnapshots($service, $item);
            $service->save();
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @return Collection<int, ServiceCatalogItem>
     */
    public static function catalogOptions(): Collection
    {
        return ServiceCatalogItem::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }
}
