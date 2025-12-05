<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('user_uuid');
            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->unsignedBigInteger('image_size_bytes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business');
    }
};
