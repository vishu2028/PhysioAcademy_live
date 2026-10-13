<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->enum('type', ['pdf', 'document', 'video', 'link', 'note'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('learning_materials', function (Blueprint $table) {
            $table->enum('type', ['pdf', 'video', 'link', 'note'])->change();
        });
    }
};
