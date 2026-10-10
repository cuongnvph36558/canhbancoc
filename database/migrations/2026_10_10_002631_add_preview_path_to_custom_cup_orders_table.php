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
        if (! Schema::hasColumn('custom_cup_orders', 'preview_path')) {
            Schema::table('custom_cup_orders', function (Blueprint $table): void {
                $table->string('preview_path')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The original table migration also defines this column.
    }
};
