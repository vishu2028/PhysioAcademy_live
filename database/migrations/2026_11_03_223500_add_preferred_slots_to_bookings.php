<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doubt_session_bookings', function (Blueprint $table): void {
            $table->json('preferred_slots')->nullable()->after('doubt');
        });

        Schema::table('group_webinar_requests', function (Blueprint $table): void {
            $table->json('preferred_slots')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('group_webinar_requests', function (Blueprint $table): void {
            $table->dropColumn('preferred_slots');
        });

        Schema::table('doubt_session_bookings', function (Blueprint $table): void {
            $table->dropColumn('preferred_slots');
        });
    }
};
