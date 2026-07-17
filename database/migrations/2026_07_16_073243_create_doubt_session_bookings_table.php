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
        Schema::create('doubt_session_bookings', function (Blueprint $table) {
            $table->id();

            /*
             * Public/admin friendly unique booking reference.
             * Example: DS-20260716-ABC123
             */
            $table->string('booking_reference', 50)->unique();

            /*
             * Student information is stored separately as a snapshot.
             * This preserves booking information even if the user later
             * changes their profile details.
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('student_name');
            $table->string('student_email')->index();
            $table->string('phone', 50);

            /*
             * Academic information.
             */
            $table->foreignId('academic_year_id')
                ->nullable()
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->foreignId('subject_id')
                ->nullable()
                ->constrained('subjects')
                ->nullOnDelete();

            $table->string('topic');
            $table->text('doubt');

            /*
             * Store price and duration as snapshots.
             *
             * Even if the admin changes settings later, old bookings
             * will retain their original price and duration.
             */
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_free')->default(false);

            /*
             * payment_status values:
             * not_required, pending, paid, failed, refunded
             */
            $table->string('payment_status', 30)->default('pending');

            /*
             * booking_status values:
             * pending_payment, pending_schedule, confirmed,
             * completed, cancelled
             */
            $table->string('booking_status', 30)
                ->default('pending_payment');

            /*
             * Razorpay details.
             */
            $table->string('razorpay_order_id', 100)
                ->nullable()
                ->unique();

            $table->string('razorpay_payment_id', 100)
                ->nullable()
                ->unique();

            $table->string('razorpay_signature')
                ->nullable();

            $table->text('payment_error')
                ->nullable();

            $table->timestamp('paid_at')
                ->nullable();

            /*
             * Admin scheduling details.
             */
            $table->timestamp('scheduled_at')
                ->nullable();

            $table->string('meeting_link', 2048)
                ->nullable();

            $table->text('admin_notes')
                ->nullable();

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
             * Helpful admin listing indexes.
             */
            $table->index('payment_status');
            $table->index('booking_status');
            $table->index('scheduled_at');
            $table->index(['booking_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doubt_session_bookings');
    }
};
