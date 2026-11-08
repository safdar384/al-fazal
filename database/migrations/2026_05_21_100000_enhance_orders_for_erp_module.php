<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('converted_invoice_no', 50)->nullable()->after('status');
            $table->text('notes')->nullable()->after('comments');
            $table->index('status', 'orders_status_index');
            $table->index('orderDate', 'orders_order_date_index');
            $table->index('customerCode', 'orders_customer_code_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('remarks', 500)->nullable()->after('amount');
            $table->index('productCode', 'order_items_product_code_index');
        });

        DB::table('orders')->where('status', 'Active')->update(['status' => 'Pending']);
        DB::table('orders')->where('status', 'Approved')->update(['status' => 'Confirmed']);
        DB::table('orders')->where('status', 'Rejected')->update(['status' => 'Cancelled']);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_product_code_index');
            $table->dropColumn('remarks');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_order_date_index');
            $table->dropIndex('orders_customer_code_index');
            $table->dropColumn(['converted_invoice_no', 'notes']);
        });
    }
};
