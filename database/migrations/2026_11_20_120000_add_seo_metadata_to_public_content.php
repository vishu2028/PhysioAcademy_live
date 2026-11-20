<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'topics',
        'learning_materials',
        'exam_aid_materials',
        'subjects',
        'academic_years',
        'units',
        'exam_aids',
        'exam_aid_categories',
        'pages',
        'webinars',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($this->columnsFor($tableName) as $columnName => $definition) {
                if (! Schema::hasColumn($tableName, $columnName)) {
                    Schema::table($tableName, $definition);
                }
            }
        }
    }

    public function down(): void
    {
    }

    private function columnsFor(string $tableName): array
    {
        $columns = [
            'seo_title' => fn (Blueprint $table) => $table->string('seo_title')->nullable(),
            'meta_description' => fn (Blueprint $table) => $table->string('meta_description', 500)->nullable(),
            'meta_keywords' => fn (Blueprint $table) => $table->text('meta_keywords')->nullable(),
            'seo_slug' => fn (Blueprint $table) => $table->string('seo_slug')->nullable(),
            'canonical_url' => fn (Blueprint $table) => $table->string('canonical_url', 2048)->nullable(),
            'og_title' => fn (Blueprint $table) => $table->string('og_title')->nullable(),
            'og_description' => fn (Blueprint $table) => $table->string('og_description', 500)->nullable(),
            'og_image' => fn (Blueprint $table) => $table->string('og_image', 2048)->nullable(),
            'robots_index' => fn (Blueprint $table) => $table->boolean('robots_index')->default(true),
            'robots_follow' => fn (Blueprint $table) => $table->boolean('robots_follow')->default(true),
        ];

        if ($tableName === 'pages') {
            unset($columns['meta_description']);
        }

        return $columns;
    }
};
