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
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number', 32)->unique();
            $table->string('customer_name', 120);
            $table->string('customer_email');
            $table->string('customer_phone', 30);
            $table->string('shipping_address', 500);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('order_status', 32)->default('pending')->index();
            $table->string('payment_status', 32)->default('pending')->index();
            $table->string('payment_method', 32)->default('vnpay');
            $table->string('vnp_transaction_no')->nullable();
            $table->string('vnp_bank_code')->nullable();
            $table->boolean('inventory_reserved')->default(true);
            $table->timestamp('expires_at')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
