<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            if (! Schema::hasColumn('topics', 'parent_topic_id')) {
                $table->foreignId('parent_topic_id')
                    ->nullable()
                    ->after('unit_topic_id')
                    ->constrained('parent_topics')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            if (Schema::hasColumn('topics', 'parent_topic_id')) {
                $table->dropConstrainedForeignId('parent_topic_id');
            }
        });
    }
};
