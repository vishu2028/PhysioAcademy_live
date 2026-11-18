<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->index(['support_ticket_id', 'is_public', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
    }
};
