<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_cup_orders', function (Blueprint $table): void {
            $table->json('artwork_paths')->nullable()->after('design');
        });
    }

    public function down(): void
    {
        Schema::table('custom_cup_orders', function (Blueprint $table): void {
            $table->dropColumn('artwork_paths');
        });
    }
};
