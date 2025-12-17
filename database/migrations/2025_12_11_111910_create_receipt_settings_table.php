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
            $table->integer('template_id')->default(0);
            $table->text('qrcode_data')->nullable();
            $table->text('footer_message')->nullable();
            $table->boolean('include_image')->default(false);
            $table->string('transaction_prefix', 10)->nullable();
            $table->unsignedInteger('transaction_next_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_settings');
    }
};
