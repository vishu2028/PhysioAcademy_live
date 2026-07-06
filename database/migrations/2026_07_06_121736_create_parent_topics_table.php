<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_topics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnDelete();

            $table->foreignId('unit_topic_id')
                ->constrained('unit_topics')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['unit_topic_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_topics');
    }
};
