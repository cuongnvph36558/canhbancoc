<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_cup_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('unit_price');
            $table->string('size', 20);
            $table->unsignedSmallInteger('bulk_discount_minimum');
            $table->unsignedTinyInteger('bulk_discount_percentage');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_cup_settings');
    }
};
