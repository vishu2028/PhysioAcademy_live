<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_webinar_requests', function (Blueprint $table): void {
            $table->string('organizer_whatsapp', 16)->nullable()->after('organizer_email');
            $table->decimal('total_amount', 10, 2)->default(0)->after('price_per_person');
            $table->string('policy_version', 20)->nullable()->after('admin_response');
            $table->timestamp('terms_accepted_at')->nullable()->after('policy_version');
        });

        Schema::table('group_webinar_participants', function (Blueprint $table): void {
            $table->string('policy_version', 20)->nullable()->after('invitation_sent_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('policy_version');
        });

        DB::table('group_webinar_requests')
            ->select(['id', 'participant_count', 'price_per_person', 'is_free'])
            ->orderBy('id')
            ->chunkById(100, function ($requests): void {
                foreach ($requests as $request) {
                    $total = (float) DB::table('group_webinar_participants')
                        ->where('group_webinar_request_id', $request->id)
                        ->sum('amount');
                    if ($total <= 0 && ! $request->is_free) {
                        $total = (float) $request->price_per_person * (int) $request->participant_count;
                    }

                    DB::table('group_webinar_requests')
                        ->where('id', $request->id)
                        ->update(['total_amount' => $total]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('group_webinar_participants', function (Blueprint $table): void {
            $table->dropColumn(['policy_version', 'terms_accepted_at']);
        });

        Schema::table('group_webinar_requests', function (Blueprint $table): void {
            $table->dropColumn(['organizer_whatsapp', 'total_amount', 'policy_version', 'terms_accepted_at']);
        });
    }
};
