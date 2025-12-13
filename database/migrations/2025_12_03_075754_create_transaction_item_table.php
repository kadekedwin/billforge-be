<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_item', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('transaction_uuid');
            $table->foreign('transaction_uuid')->references('uuid')->on('transaction')->cascadeOnDelete();
            $table->uuid('item_uuid');
            $table->foreign('item_uuid')->references('uuid')->on('item')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->decimal('base_price', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total_price', 12, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_item');
    }
};
