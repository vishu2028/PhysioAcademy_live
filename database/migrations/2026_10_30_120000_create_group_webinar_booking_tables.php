<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_webinar_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference', 50)->unique();
            $table->string('organizer_name');
            $table->string('organizer_email')->index();
            $table->string('topic', 255);
            $table->text('description');
            $table->unsignedSmallInteger('participant_count');
            $table->unsignedSmallInteger('minimum_participants');
            $table->unsignedSmallInteger('maximum_participants');
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('price_per_person', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_free')->default(false);
            $table->string('status', 30)->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('meeting_link', 2048)->nullable();
            $table->text('admin_response')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'gw_requests_status_created_idx');
        });

        Schema::create('group_webinar_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_webinar_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('email_normalized');
            $table->string('role', 20)->default('participant');
            $table->string('status', 30)->default('invitation_sent');
            $table->string('payment_status', 30)->default('pending');
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->string('coupon_code', 64)->nullable();
            $table->char('invitation_token_hash', 64)->nullable()->unique();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['group_webinar_request_id', 'email_normalized'], 'group_webinar_participant_email_unique');
            $table->index(['group_webinar_request_id', 'payment_status'], 'gw_participants_request_payment_idx');
        });

        Schema::create('group_webinar_participant_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('participant_id')->constrained('group_webinar_participants')->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('public_reference')->unique();
            $table->string('coupon_code', 64)->nullable();
            $table->decimal('original_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status', 30)->default('pending');
            $table->string('razorpay_order_id', 100)->nullable()->unique();
            $table->string('razorpay_payment_id', 100)->nullable()->unique();
            $table->string('razorpay_signature')->nullable();
            $table->text('payment_error')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['participant_id', 'status'], 'gw_payment_participant_status_idx');
        });

        Schema::create('group_webinar_email_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('participant_id')->constrained('group_webinar_participants')->cascadeOnDelete();
            $table->string('delivery_key')->unique();
            $table->string('notification_type', 40);
            $table->string('recipient');
            $table->string('subject');
            $table->string('title');
            $table->text('message');
            $table->text('action_url')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamps();

            $table->index(['participant_id', 'created_at'], 'gw_email_participant_created_idx');
        });

        Schema::table('coupon_redemptions', function (Blueprint $table): void {
            $table->string('email')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table): void {
            $table->dropIndex(['email']);
            $table->dropColumn('email');
        });

        Schema::dropIfExists('group_webinar_email_deliveries');
        Schema::dropIfExists('group_webinar_participant_payments');
        Schema::dropIfExists('group_webinar_participants');
        Schema::dropIfExists('group_webinar_requests');
    }
};
