<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_catagory')) {
            Schema::create('customer_catagory', function (Blueprint $table) {
                $table->id();
                $table->string('catagoryName')->unique();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_catagory');
    }
};
