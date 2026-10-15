<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_colors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('hex_code', 7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'hex_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
