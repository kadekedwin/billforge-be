<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipt_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('business_uuid')->unique();
            $table->foreign('business_uuid')->references('uuid')->on('business')->cascadeOnDelete();
            $table->text('qrcode_data')->nullable();
            $table->text('footer_message')->nullable();
            $table->boolean('include_image')->default(false);
            $table->string('transaction_prefix', 10)->nullable();
            $table->unsignedInteger('transaction_next_number')->default(1);
            $table->integer('receipt_style_id')->default(0);
            $table->string('printer_font', 50)->nullable();
            $table->string('line_character', 5)->nullable();
            $table->integer('item_layout')->default(0);
            $table->string('label_receipt_id', 100)->nullable();
            $table->boolean('label_receipt_id_enabled')->default(true);
            $table->string('label_transaction_id', 100)->nullable();
            $table->boolean('label_transaction_id_enabled')->default(true);
            $table->string('label_date', 100)->nullable();
            $table->boolean('label_date_enabled')->default(true);
            $table->string('label_time', 100)->nullable();
            $table->boolean('label_time_enabled')->default(true);
            $table->string('label_cashier', 100)->nullable();
            $table->boolean('label_cashier_enabled')->default(true);
            $table->string('label_customer', 100)->nullable();
            $table->boolean('label_customer_enabled')->default(true);
            $table->string('label_items', 100)->nullable();
            $table->boolean('label_items_enabled')->default(true);
            $table->string('label_subtotal', 100)->nullable();
            $table->boolean('label_subtotal_enabled')->default(true);
            $table->string('label_discount', 100)->nullable();
            $table->boolean('label_discount_enabled')->default(true);
            $table->string('label_tax', 100)->nullable();
            $table->boolean('label_tax_enabled')->default(true);
            $table->string('label_total', 100)->nullable();
            $table->boolean('label_total_enabled')->default(true);
            $table->string('label_payment_method', 100)->nullable();
            $table->boolean('label_payment_method_enabled')->default(true);
            $table->string('label_amount_paid', 100)->nullable();
            $table->boolean('label_amount_paid_enabled')->default(true);
            $table->string('label_change', 100)->nullable();
            $table->boolean('label_change_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_settings');
    }
};
