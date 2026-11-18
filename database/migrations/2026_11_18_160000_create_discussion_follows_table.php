<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_follows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('followable_type');
            $table->unsignedBigInteger('followable_id');
            $table->timestamps();

            $table->unique(
                ['user_id', 'followable_type', 'followable_id'],
                'discussion_follows_user_target_unique'
            );
            $table->index(
                ['followable_type', 'followable_id'],
                'discussion_follows_target_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_follows');
    }
};
