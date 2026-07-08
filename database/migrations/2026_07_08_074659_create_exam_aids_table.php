<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamAidsTable extends Migration
{
    public function up()
    {
        Schema::create('exam_aids', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade');

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->onDelete('set null');

            $table->foreignId('academic_year_id')
                ->nullable()
                ->constrained('academic_years')
                ->onDelete('set null');

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters')
                ->onDelete('set null');

            $table->string('title');
            $table->text('description')->nullable();

            $table->longText('viva_question')->nullable();
            $table->longText('exam_question')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_aids');
    }
}
