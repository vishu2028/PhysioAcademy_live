<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webinars', function (Blueprint $table): void {
            $table->string('subject')->nullable()->after('title');
            $table->string('academic_year')->nullable()->after('subject');
            $table->string('semester')->nullable()->after('academic_year');
            $table->text('learning_objectives')->nullable()->after('description');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('ends_at');
            $table->string('meeting_platform')->nullable()->after('timezone');
            $table->timestamp('registration_deadline')->nullable()->after('published_at');
            $table->index(['subject', 'academic_year', 'semester']);
            $table->index('registration_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('webinars', function (Blueprint $table): void {
            $table->dropIndex(['subject', 'academic_year', 'semester']);
            $table->dropIndex(['registration_deadline']);
            $table->dropColumn([
                'subject',
                'academic_year',
                'semester',
                'learning_objectives',
                'duration_minutes',
                'meeting_platform',
                'registration_deadline',
            ]);
        });
    }
};
