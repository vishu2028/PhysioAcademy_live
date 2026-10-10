<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_prep_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('edit');
            $table->string('button_text')->default('Open');
            $table->foreignId('exam_aid_category_id')
                ->nullable()
                ->constrained('exam_aid_categories')
                ->nullOnDelete();
            $table->string('custom_url')->nullable();
            $table->boolean('is_large')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the cards that were previously hard-coded on the homepage.
        $now = now();

        $cards = [
            ['Question Bank', 'Year-wise, subject-wise organized questions with pattern analysis and frequency mapping.', 'edit', 'Open Question Bank', true],
            ['Viva Mastery', 'Most asked viva questions by examiners across universities with model answers.', 'mic', 'Practice Viva', false],
            ['Expected Questions', 'AI-assisted prediction of most likely exam questions based on trend analysis.', 'trending', 'View Expected', false],
            ['Important Topics', 'Curated list of must-prepare topics that consistently appear in university exams.', 'check', 'See Topics', false],
            ['Exam Tips', 'Proven exam strategies, time management, answer presentation, and scoring techniques.', 'lightbulb', 'Get Tips', false],
            ['Study Strategies', 'Subject-wise study plans, spaced repetition schedules, and revision timetables.', 'calendar', 'Plan Study', false],
        ];

        foreach ($cards as $index => [$title, $description, $icon, $button, $large]) {
            DB::table('exam_prep_cards')->insert([
                'title' => $title,
                'description' => $description,
                'icon' => $icon,
                'button_text' => $button,
                'is_large' => $large,
                'is_active' => true,
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_prep_cards');
    }
};
