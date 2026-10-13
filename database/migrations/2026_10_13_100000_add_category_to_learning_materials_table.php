<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->foreignId('exam_aid_category_id')
                ->nullable()
                ->after('topic_id')
                ->constrained('exam_aid_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exam_aid_category_id');
        });
    }
};
