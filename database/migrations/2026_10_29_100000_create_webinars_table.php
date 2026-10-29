<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('speaker')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('timezone', 64)->default('Asia/Kolkata');
            $table->string('meeting_link', 2048)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_free')->default(true);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'starts_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('webinars'); }
};
