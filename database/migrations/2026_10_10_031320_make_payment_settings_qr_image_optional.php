<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->string('qr_image_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_settings')
            ->whereNull('qr_image_path')
            ->update(['qr_image_path' => '']);

        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->string('qr_image_path')->nullable(false)->change();
        });
    }
};
