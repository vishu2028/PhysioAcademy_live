<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doubt_session_follow_ups', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('doubt_session_booking_id')
                ->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->after('user_id')
                ->constrained('doubt_session_follow_ups')->cascadeOnDelete();
            $table->unsignedBigInteger('thread_id')->nullable()->after('parent_id');
            $table->string('author_role', 20)->nullable()->after('thread_id');
            $table->uuid('submission_key')->nullable()->after('author_role');
            $table->boolean('is_hidden')->default(false)->after('submission_key');
            $table->index(['thread_id', 'parent_id', 'created_at'], 'doubt_session_follow_ups_thread_created_idx');
            $table->unique(['user_id', 'submission_key'], 'doubt_session_follow_ups_user_submission_unique');
            $table->foreignId('doubt_session_booking_id')->nullable()->change();
        });

        DB::table('doubt_session_follow_ups')
            ->whereNull('user_id')
            ->update([
                'user_id' => DB::raw(
                    '(SELECT user_id FROM doubt_session_bookings WHERE doubt_session_bookings.id = doubt_session_follow_ups.doubt_session_booking_id)'
                ),
                'thread_id' => DB::raw('id'),
                'author_role' => 'user',
            ]);

        DB::table('doubt_session_follow_ups')
            ->whereNotNull('answer')
            ->orderBy('id')
            ->chunkById(100, function ($followUps): void {
                foreach ($followUps as $followUp) {
                    DB::table('doubt_session_follow_ups')->insert([
                        'doubt_session_booking_id' => $followUp->doubt_session_booking_id,
                        'user_id' => $followUp->answered_by,
                        'parent_id' => $followUp->id,
                        'thread_id' => $followUp->id,
                        'author_role' => 'admin',
                        'question' => $followUp->answer,
                        'status' => 'pending',
                        'created_at' => $followUp->answered_at ?? $followUp->updated_at,
                        'updated_at' => $followUp->answered_at ?? $followUp->updated_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (
            DB::table('doubt_session_follow_ups')->whereNull('doubt_session_booking_id')->exists()
            || DB::table('doubt_session_follow_ups')->whereNotNull('parent_id')->exists()
            || DB::table('doubt_session_follow_ups')->where('is_hidden', true)->exists()
        ) {
            throw new RuntimeException(
                'Cannot roll back follow-up discussions while community posts, replies, or moderated content exist; preserve their records before rolling back.'
            );
        }

        Schema::table('doubt_session_follow_ups', function (Blueprint $table): void {
            $table->dropUnique('doubt_session_follow_ups_user_submission_unique');
            $table->dropIndex('doubt_session_follow_ups_thread_created_idx');
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'parent_id',
                'thread_id',
                'author_role',
                'submission_key',
                'is_hidden',
            ]);
            $table->foreignId('doubt_session_booking_id')->nullable(false)->change();
        });
    }
};
