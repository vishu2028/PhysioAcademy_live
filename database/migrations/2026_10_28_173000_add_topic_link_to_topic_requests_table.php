<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('topic_requests', function (Blueprint $table) {
            $table->string('topic_link', 2048)->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('topic_requests', function (Blueprint $table) {
            $table->dropColumn('topic_link');
        });
    }
};
