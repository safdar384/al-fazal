<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->string('productCode'); // finished good code
            $table->decimal('batch_size', 18, 6)->default(1); // output units
            $table->decimal('wastage_percent', 8, 4)->default(0); // overall wastage
            $table->string('status', 16)->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['productCode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
