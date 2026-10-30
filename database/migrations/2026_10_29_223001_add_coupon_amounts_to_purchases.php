<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doubt_session_bookings', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 64)->nullable();
            $table->decimal('original_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
        });

        Schema::table('webinar_registrations', function (Blueprint $table): void {
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code', 64)->nullable();
            $table->decimal('original_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
        });

        DB::table('doubt_session_bookings')->update([
            'original_amount' => DB::raw('amount'),
        ]);
        DB::table('webinar_registrations')->update([
            'original_amount' => DB::raw('amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('webinar_registrations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['coupon_code', 'original_amount', 'discount_amount']);
        });

        Schema::table('doubt_session_bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['coupon_code', 'original_amount', 'discount_amount']);
        });
    }
};
