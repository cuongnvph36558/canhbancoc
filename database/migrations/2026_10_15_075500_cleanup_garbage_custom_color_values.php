<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace any garbage placeholder values (#FF00FF, DEFAULT_SET) that
     * were saved on order_items.custom_color by a buggy product form.
     * For each affected row we resolve the first active color of the
     * related product (matching the CartController fallback behavior).
     */
    public function up(): void
    {
        $placeholderValues = ['#FF00FF', 'DEFAULT_SET'];

        $badRows = DB::table('order_items')
            ->whereIn('custom_color', $placeholderValues)
            ->whereNotNull('product_id')
            ->select('id', 'product_id', 'custom_color')
            ->get();

        foreach ($badRows as $row) {
            $firstColor = DB::table('product_colors')
                ->where('product_id', $row->product_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();

            if ($firstColor === null) {
                continue;
            }

            DB::table('order_items')
                ->where('id', $row->id)
                ->update(['custom_color' => $firstColor->hex_code]);
        }
    }

    public function down(): void
    {
        // No-op: this is a one-way data cleanup. We do not restore the
        // garbage values because they were never real customer choices.
    }
};
