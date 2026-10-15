<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_cup_settings', function (Blueprint $table): void {
            $table->unsignedSmallInteger('print_area_width_mm')->default(220)->after('size');
            $table->unsignedSmallInteger('print_area_height_mm')->default(90)->after('print_area_width_mm');
        });
    }

    public function down(): void
    {
        Schema::table('custom_cup_settings', function (Blueprint $table): void {
            $table->dropColumn(['print_area_width_mm', 'print_area_height_mm']);
        });
    }
};
