<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_aid_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('exam_aid_materials', function (Blueprint $table) {
            $table->foreignId('exam_aid_category_id')
                ->nullable()
                ->after('exam_aid_id')
                ->constrained('exam_aid_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_aid_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exam_aid_category_id');
        });

        Schema::dropIfExists('exam_aid_categories');
    }
};
