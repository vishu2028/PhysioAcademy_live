<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->string('title_highlight')->nullable()->after('title');
        });

        // Keep the homepage unchanged: the highlighted text used to be whatever followed "for" in the title.
        foreach (DB::table('hero_sections')->get(['id', 'title']) as $hero) {
            $parts = explode('for', (string) $hero->title, 2);

            if (isset($parts[1]) && trim($parts[1]) !== '') {
                DB::table('hero_sections')
                    ->where('id', $hero->id)
                    ->update(['title_highlight' => trim($parts[1])]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn('title_highlight');
        });
    }
};
