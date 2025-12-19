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
            $table->integer('image_template_id')->default(0);
            $table->text('qrcode_data')->nullable();
            $table->text('footer_message')->nullable();
            $table->boolean('include_image')->default(false);
            $table->string('transaction_prefix', 10)->nullable();
            $table->unsignedInteger('transaction_next_number')->default(1);
            $table->string('label_receipt_id', 100)->nullable();
            $table->string('label_transaction_id', 100)->nullable();
            $table->string('label_date', 100)->nullable();
            $table->string('label_time', 100)->nullable();
            $table->string('label_cashier', 100)->nullable();
            $table->string('label_customer', 100)->nullable();
            $table->string('label_items', 100)->nullable();
            $table->string('label_subtotal', 100)->nullable();
            $table->string('label_discount', 100)->nullable();
            $table->string('label_tax', 100)->nullable();
            $table->string('label_total', 100)->nullable();
            $table->string('label_payment_method', 100)->nullable();
            $table->string('label_amount_paid', 100)->nullable();
            $table->string('label_change', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_settings');
    }
};
