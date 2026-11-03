<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('group_webinar_adjustment_requests')) {
            Schema::create('group_webinar_adjustment_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('group_webinar_request_id');
                $table->foreignId('participant_id')->nullable();
                $table->foreignId('payment_id')->nullable();
                $table->foreignId('requested_by')->nullable();
                $table->foreignId('processed_by')->nullable();
                $table->string('type', 30);
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('status', 30)->default('pending');
                $table->string('provider_reference', 100)->nullable();
                $table->text('admin_response')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('group_webinar_adjustment_requests', ['provider_reference'], 'unique')) {
            Schema::table('group_webinar_adjustment_requests', function (Blueprint $table): void {
                $table->unique('provider_reference', 'gw_adj_provider_ref_unique');
            });
        }

        if (! Schema::hasIndex('group_webinar_adjustment_requests', ['group_webinar_request_id', 'type', 'status'])) {
            Schema::table('group_webinar_adjustment_requests', function (Blueprint $table): void {
                $table->index(
                    ['group_webinar_request_id', 'type', 'status'],
                    'gw_adjustment_request_type_status_idx'
                );
            });
        }

        $foreignKeys = collect(Schema::getForeignKeys('group_webinar_adjustment_requests'))
            ->pluck('columns')
            ->flatten()
            ->all();

        Schema::table('group_webinar_adjustment_requests', function (Blueprint $table) use ($foreignKeys): void {
            if (! in_array('group_webinar_request_id', $foreignKeys, true)) {
                $table->foreign('group_webinar_request_id', 'gw_adj_request_fk')
                    ->references('id')
                    ->on('group_webinar_requests')
                    ->cascadeOnDelete();
            }

            if (! in_array('participant_id', $foreignKeys, true)) {
                $table->foreign('participant_id', 'gw_adj_participant_fk')
                    ->references('id')
                    ->on('group_webinar_participants')
                    ->nullOnDelete();
            }

            if (! in_array('payment_id', $foreignKeys, true)) {
                $table->foreign('payment_id', 'gw_adj_payment_fk')
                    ->references('id')
                    ->on('group_webinar_participant_payments')
                    ->nullOnDelete();
            }

            if (! in_array('requested_by', $foreignKeys, true)) {
                $table->foreign('requested_by', 'gw_adj_requester_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }

            if (! in_array('processed_by', $foreignKeys, true)) {
                $table->foreign('processed_by', 'gw_adj_processor_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_webinar_adjustment_requests');
    }
};
