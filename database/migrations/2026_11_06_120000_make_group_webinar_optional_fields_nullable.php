<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_webinar_requests', function (Blueprint $table): void {
            $table->string('topic', 255)->nullable()->change();
            $table->mediumText('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('group_webinar_requests', function (Blueprint $table): void {
            $table->string('topic', 255)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });
    }
};
