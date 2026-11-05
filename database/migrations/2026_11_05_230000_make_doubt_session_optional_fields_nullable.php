<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doubt_session_bookings', function (Blueprint $table): void {
            $table->string('topic')->nullable()->change();
            $table->text('doubt')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('doubt_session_bookings', function (Blueprint $table): void {
            $table->string('topic')->nullable(false)->change();
            $table->text('doubt')->nullable(false)->change();
        });
    }
};
