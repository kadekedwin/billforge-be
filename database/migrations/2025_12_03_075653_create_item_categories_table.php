<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');
            $table->uuid('category_uuid');
            $table->foreign('item_uuid')->references('uuid')->on('item')->cascadeOnDelete();
            $table->foreign('category_uuid')->references('uuid')->on('categories')->cascadeOnDelete();
            $table->unique(['item_uuid', 'category_uuid']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_categories');
    }
};
