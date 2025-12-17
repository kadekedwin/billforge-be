<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('printer_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('business_uuid')->unique();
            $table->foreign('business_uuid')->references('uuid')->on('business')->cascadeOnDelete();
            $table->integer('paper_width_mm');
            $table->integer('chars_per_line');
            $table->string('encoding', 50);
            $table->integer('feed_lines')->default(3);
            $table->boolean('cut_enabled')->default(true);
            $table->boolean('auto_print')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printer_settings');
    }
};
