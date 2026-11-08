<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('grandTotal');
            $table->string('payment_status', 20)->default('unpaid')->after('paid_amount');
            $table->unsignedInteger('corten')->default(0)->after('balance');
            $table->decimal('biltyExpanse', 15, 2)->default(0)->after('corten');
            $table->decimal('loaderExpanse', 15, 2)->default(0)->after('biltyExpanse');
            $table->decimal('biltyBalance', 15, 2)->default(0)->after('loaderExpanse');
            $table->index('supplierCode', 'purchase_invoices_supplier_code_index');
            $table->index('invoiceDate', 'purchase_invoices_invoice_date_index');
            $table->index('payment_status', 'purchase_invoices_payment_status_index');
        });

        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->index('productCode', 'purchase_invoice_items_product_code_index');
        });

        Schema::create('purchase_invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
            $table->string('invoiceNo', 50);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('cash');
            $table->string('reference_no', 100)->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('invoiceNo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_payments');

        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropIndex('purchase_invoice_items_product_code_index');
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropIndex('purchase_invoices_supplier_code_index');
            $table->dropIndex('purchase_invoices_invoice_date_index');
            $table->dropIndex('purchase_invoices_payment_status_index');
            $table->dropColumn([
                'paid_amount', 'payment_status', 'corten',
                'biltyExpanse', 'loaderExpanse', 'biltyBalance',
            ]);
        });
    }
};
