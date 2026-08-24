<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_aid_banners', function (Blueprint $table) {
            $table->id();

            $table->string('main_title');
            $table->text('description')->nullable();

            $table->string('title_1')->nullable();
            $table->string('title_2')->nullable();

            $table->string('heading_1')->nullable();
            $table->unsignedInteger('percentage_value')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_aid_banners');
    }
};
