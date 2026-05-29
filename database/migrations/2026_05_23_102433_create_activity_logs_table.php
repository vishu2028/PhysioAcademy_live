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
        Schema::create('activity_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->string('action'); // CREATE, UPDATE, DELETE, LOGIN, LOGOUT, etc.
            $blueprint->string('module'); // Users, Pages, Settings, etc.
            $blueprint->text('description')->nullable();
            $blueprint->string('ip_address', 45)->nullable();
            $blueprint->string('user_agent')->nullable();
            $blueprint->json('properties')->nullable(); // For storing raw data diffs if needed
            $blueprint->timestamps();

            $blueprint->index(['user_id', 'action', 'module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
