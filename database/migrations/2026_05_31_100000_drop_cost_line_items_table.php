<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cost_line_items');
    }

    public function down(): void
    {
        // Kostenpositionen wurden zugunsten von project_services entfernt.
    }
};
