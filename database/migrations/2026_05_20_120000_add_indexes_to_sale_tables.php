<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->index('customerCode', 'sale_invoices_customer_code_index');
            $table->index('invoiceDate', 'sale_invoices_invoice_date_index');
            $table->index(['invoiceDate', 'customerCode'], 'sale_invoices_date_customer_index');
        });

        Schema::table('sale_invoice_items', function (Blueprint $table) {
            $table->index('sale_invoice_id', 'sale_invoice_items_invoice_id_index');
            $table->index('invoiceNo', 'sale_invoice_items_invoice_no_index');
            $table->index('productCode', 'sale_invoice_items_product_code_index');
        });
    }

    public function down(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->dropIndex('sale_invoices_customer_code_index');
            $table->dropIndex('sale_invoices_invoice_date_index');
            $table->dropIndex('sale_invoices_date_customer_index');
        });

        Schema::table('sale_invoice_items', function (Blueprint $table) {
            $table->dropIndex('sale_invoice_items_invoice_id_index');
            $table->dropIndex('sale_invoice_items_invoice_no_index');
            $table->dropIndex('sale_invoice_items_product_code_index');
        });
    }
};
