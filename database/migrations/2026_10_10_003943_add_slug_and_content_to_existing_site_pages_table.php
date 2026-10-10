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
        if (! Schema::hasTable('site_pages')) {
            return;
        }

        if (! Schema::hasColumn('site_pages', 'slug')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->string('slug')->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('site_pages', 'content')) {
            Schema::table('site_pages', function (Blueprint $table): void {
                $table->json('content')->nullable();
            });
        }

        DB::table('site_pages')
            ->whereNull('slug')
            ->orderBy('id')
            ->get(['id', 'content'])
            ->each(function (object $page): void {
                DB::table('site_pages')
                    ->where('id', $page->id)
                    ->update([
                        'slug' => 'legacy-'.$page->id,
                        'content' => $page->content ?? '{}',
                    ]);
            });

        DB::table('site_pages')
            ->whereNull('content')
            ->update(['content' => '{}']);
    }

    /**
     * This migration is intentionally irreversible to preserve stored page content.
     */
    public function down(): void {}
};
