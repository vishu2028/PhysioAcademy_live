<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamAidMaterialsTable extends Migration
{
    public function up()
    {
        Schema::create('exam_aid_materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_aid_id')
                ->constrained('exam_aids')
                ->onDelete('cascade');

            $table->string('title');
            $table->string('type')->default('pdf'); // pdf, video, link, note

            $table->string('file_path')->nullable();
            $table->text('url')->nullable();
            $table->longText('content')->nullable();

            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_aid_materials');
    }
}
