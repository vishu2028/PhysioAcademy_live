<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FollowUpMigrationSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_follow_ups_and_admin_answers_are_backfilled_without_data_loss(): void
    {
        $originalDefault = config('database.default');
        config([
            'database.connections.follow_up_migration_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'follow_up_migration_test',
        ]);
        DB::purge('follow_up_migration_test');

        try {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
            });
            Schema::create('doubt_session_bookings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            });
            Schema::create('doubt_session_follow_ups', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('doubt_session_booking_id')->constrained()->cascadeOnDelete();
                $table->text('question');
                $table->text('answer')->nullable();
                $table->string('status', 20)->default('pending');
                $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('answered_at')->nullable();
                $table->timestamps();
            });

            DB::table('users')->insert([
                ['id' => 1, 'name' => 'Student'],
                ['id' => 2, 'name' => 'Admin'],
            ]);
            DB::table('doubt_session_bookings')->insert(['id' => 10, 'user_id' => 1]);
            DB::table('doubt_session_follow_ups')->insert([
                'id' => 20,
                'doubt_session_booking_id' => 10,
                'question' => 'Existing student question',
                'answer' => 'Existing admin answer',
                'status' => 'answered',
                'answered_by' => 2,
                'answered_at' => '2026-11-10 12:00:00',
                'created_at' => '2026-11-09 12:00:00',
                'updated_at' => '2026-11-10 12:00:00',
            ]);

            $migration = require database_path(
                'migrations/2026_11_16_120000_extend_doubt_session_follow_ups_for_discussions.php'
            );
            $migration->up();

            $this->assertSame(2, DB::table('doubt_session_follow_ups')->count());
            $this->assertDatabaseHas('doubt_session_follow_ups', [
                'id' => 20,
                'doubt_session_booking_id' => 10,
                'user_id' => 1,
                'thread_id' => 20,
                'parent_id' => null,
                'question' => 'Existing student question',
                'answer' => 'Existing admin answer',
                'answered_by' => 2,
            ], 'follow_up_migration_test');
            $this->assertDatabaseHas('doubt_session_follow_ups', [
                'doubt_session_booking_id' => 10,
                'user_id' => 2,
                'thread_id' => 20,
                'parent_id' => 20,
                'author_role' => 'admin',
                'question' => 'Existing admin answer',
                'created_at' => '2026-11-10 12:00:00',
            ], 'follow_up_migration_test');

            try {
                $migration->down();
                $this->fail('Rollback should refuse to remove converted discussion history.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('Cannot roll back follow-up discussions', $exception->getMessage());
            }

            $this->assertSame(2, DB::table('doubt_session_follow_ups')->count());
            $this->assertDatabaseHas('doubt_session_follow_ups', [
                'id' => 20,
                'question' => 'Existing student question',
                'answer' => 'Existing admin answer',
            ], 'follow_up_migration_test');
        } finally {
            DB::purge('follow_up_migration_test');
            config(['database.default' => $originalDefault]);
        }
    }
}
