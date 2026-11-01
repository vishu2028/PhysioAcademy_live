<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doubt_session_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doubt_session_booking_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['doubt_session_booking_id', 'user_id']);
        });

        Schema::create('doubt_session_follow_ups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doubt_session_booking_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('answered_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['doubt_session_booking_id', 'status', 'created_at'], 'doubt_session_follow_ups_booking_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doubt_session_follow_ups');
        Schema::dropIfExists('doubt_session_likes');
    }
};
