<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('business_uuid');
            $table->foreign('business_uuid')->references('uuid')->on('business')->cascadeOnDelete();
            $table->uuid('payment_method_uuid')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('final_amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
