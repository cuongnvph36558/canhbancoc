<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_cup_icons', function (Blueprint $table): void {
            $table->id();
            $table->string('glyph', 16)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_cup_icons');
    }
};
