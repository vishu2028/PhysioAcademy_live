<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('webinar_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webinar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_name');
            $table->string('student_email')->index();
            $table->string('registration_reference', 50)->unique();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_free')->default(true);
            $table->string('payment_status', 30)->default('not_required');
            $table->string('registration_status', 30)->default('pending');
            $table->string('razorpay_order_id', 100)->nullable()->unique();
            $table->string('razorpay_payment_id', 100)->nullable()->unique();
            $table->string('razorpay_signature')->nullable();
            $table->text('payment_error')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['webinar_id', 'user_id']);
            $table->index(['webinar_id', 'registration_status']);
        });
    }
    public function down(): void { Schema::dropIfExists('webinar_registrations'); }
};
