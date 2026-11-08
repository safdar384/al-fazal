<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('fitting_labour_materials');
        Schema::dropIfExists('fitting_labour_items');
        Schema::dropIfExists('fitting_labours');
        // Created with the fitting-labour module; only used by that module
        Schema::dropIfExists('bom_works');
        Schema::dropIfExists('production_stages');
    }

    public function down(): void
    {
        // Irreversible cleanup — recreate via earlier fitting-labour migrations if needed.
    }
};
