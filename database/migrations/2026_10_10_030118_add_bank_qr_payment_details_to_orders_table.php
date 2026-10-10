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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_bank_code', 20)->nullable();
            $table->string('payment_bank_name', 120)->nullable();
            $table->string('payment_account_number', 50)->nullable();
            $table->string('payment_account_name', 120)->nullable();
            $table->string('payment_qr_image_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_bank_code',
                'payment_bank_name',
                'payment_account_number',
                'payment_account_name',
                'payment_qr_image_path',
            ]);
        });
    }
};
