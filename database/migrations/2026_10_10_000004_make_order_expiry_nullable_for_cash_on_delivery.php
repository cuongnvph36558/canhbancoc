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
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')
            ->whereNull('expires_at')
            ->update(['expires_at' => '9999-12-31 23:59:59']);

        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable(false)->change();
        });
    }
};
