<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('labour_payments');
        Schema::dropIfExists('labour_return_items');
        Schema::dropIfExists('labour_returns');
        Schema::dropIfExists('labour_issue_items');
        Schema::dropIfExists('labour_issues');
        Schema::dropIfExists('fitter_production_transfers');
        Schema::dropIfExists('fitter_production_stages');
        Schema::dropIfExists('fitter_production_jobs');
        Schema::dropIfExists('labour_work_setups');
        Schema::dropIfExists('labour_work_types');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Fitter labour module removed; restore via original migrations if needed.
    }
};
