<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webinar_registrations', function (Blueprint $table): void {
            $table->string('attendance_status', 20)->nullable()->after('joined_at');
        });
    }

    public function down(): void
    {
        Schema::table('webinar_registrations', fn (Blueprint $table) => $table->dropColumn('attendance_status'));
    }
};
