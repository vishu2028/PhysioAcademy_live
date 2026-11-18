<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('requester_name');
            $table->string('requester_email');
            $table->uuid('source_reference');
            $table->string('rootflash_ticket_id')->nullable()->unique();
            $table->string('title');
            $table->text('description');
            $table->string('category', 100);
            $table->string('priority', 20);
            $table->string('status', 30)->default('open');
            $table->json('attachment_paths')->nullable();
            $table->char('submission_hash', 64);
            $table->string('sync_status', 20)->default('pending');
            $table->timestamps();

            $table->unique(['user_id', 'source_reference']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
