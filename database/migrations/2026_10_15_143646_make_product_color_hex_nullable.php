<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the strict unique (product_id, hex_code) constraint because we now
        // allow null hex_code for "name only" product color types.
        Schema::table('product_colors', function (Blueprint $table): void {
            $table->dropUnique(['product_id', 'hex_code']);
        });

        Schema::table('product_colors', function (Blueprint $table): void {
            $table->string('hex_code', 7)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_colors', function (Blueprint $table): void {
            $table->string('hex_code', 7)->nullable(false)->change();
        });

        Schema::table('product_colors', function (Blueprint $table): void {
            $table->unique(['product_id', 'hex_code']);
        });
    }
};
