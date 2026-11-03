<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doubt_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doubt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_role', 20);
            $table->longText('message');
            $table->timestamps();

            $table->index(['doubt_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doubt_messages');
    }
};
