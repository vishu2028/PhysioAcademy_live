<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('support_ticket_replies')) {
            $requiredColumns = [
                'id',
                'support_ticket_id',
                'source',
                'message_key',
                'author_name',
                'body',
                'is_public',
                'sync_status',
                'attachment_metadata',
                'created_at',
                'updated_at',
            ];

            if (! Schema::hasColumns('support_ticket_replies', $requiredColumns)) {
                throw new \RuntimeException(
                    'The existing support_ticket_replies table is missing expected columns; refusing to mark this migration as complete.',
                );
            }

            $indexColumns = ['support_ticket_id', 'is_public', 'created_at'];
            if (! Schema::hasIndex('support_ticket_replies', $indexColumns)) {
                Schema::table('support_ticket_replies', function (Blueprint $table) use ($indexColumns) {
                    $table->index($indexColumns, 'str_replies_ticket_public_created_idx');
                });
            }

            return;
        }

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->string('source', 20);
            $table->char('message_key', 64);
            $table->string('author_name');
            $table->text('body');
            $table->boolean('is_public')->default(false);
            $table->string('sync_status', 20)->default('synced');
            $table->json('attachment_metadata')->nullable();
            $table->timestamps();

            $table->unique(['support_ticket_id', 'message_key']);
            $table->index(
                ['support_ticket_id', 'is_public', 'created_at'],
                'str_replies_ticket_public_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
    }
};
