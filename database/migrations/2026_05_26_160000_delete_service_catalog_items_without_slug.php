<?php

use App\Models\ServiceCatalogItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_catalog_items')) {
            return;
        }

        if (! Schema::hasColumn('service_catalog_items', 'slug')) {
            return;
        }

        ServiceCatalogItem::query()
            ->whereNull('slug')
            ->whereDoesntHave('projectServices')
            ->delete();
    }

    public function down(): void
    {
        // Datenmigration ohne Wiederherstellung gelöschter Zeilen.
    }
};
