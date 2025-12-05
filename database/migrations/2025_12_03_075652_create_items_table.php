<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('item', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('business_uuid');
            $table->foreign('business_uuid')->references('uuid')->on('business')->cascadeOnDelete();
            $table->uuid('discount_uuid')->nullable();
            $table->uuid('tax_uuid')->nullable();
            $table->string('name', 255);
            $table->string('sku', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->unsignedBigInteger('image_size_bytes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item');
    }
};
