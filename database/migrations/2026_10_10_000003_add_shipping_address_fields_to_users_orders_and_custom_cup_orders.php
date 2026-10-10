<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable();
            $table->string('shipping_province', 120)->nullable();
            $table->string('shipping_ward', 120)->nullable();
            $table->string('shipping_address', 500)->nullable();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_province', 120)->nullable();
            $table->string('shipping_ward', 120)->nullable();
        });

        Schema::table('custom_cup_orders', function (Blueprint $table): void {
            $table->string('shipping_province', 120)->nullable();
            $table->string('shipping_ward', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'shipping_province', 'shipping_ward', 'shipping_address']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['shipping_province', 'shipping_ward']);
        });

        Schema::table('custom_cup_orders', function (Blueprint $table): void {
            $table->dropColumn(['shipping_province', 'shipping_ward']);
        });
    }
};
