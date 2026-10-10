<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_cup_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number', 24)->unique();
            $table->string('customer_name', 120);
            $table->string('customer_phone', 30);
            $table->string('customer_email')->nullable();
            $table->string('shipping_address', 500);
            $table->string('cup_variant', 20);
            $table->string('cup_size', 20);
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->unsignedTinyInteger('discount_percentage')->default(0);
            $table->unsignedInteger('total_amount');
            $table->json('design');
            $table->string('artwork_path')->nullable();
            $table->string('preview_path')->nullable();
            $table->string('status', 24)->default('pending');
            $table->index(['status', 'created_at']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_cup_orders');
    }
};
