<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid')->after('sale_type');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_status');
            $table->index('payment_status', 'sale_invoices_payment_status_index');
        });

        DB::table('sale_invoices')
            ->where('sale_type', 'cash')
            ->update([
                'payment_status' => 'paid',
                'paid_amount' => DB::raw('grandTotal'),
            ]);
    }

    public function down(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->dropIndex('sale_invoices_payment_status_index');
            $table->dropColumn(['payment_status', 'paid_amount']);
        });
    }
};
