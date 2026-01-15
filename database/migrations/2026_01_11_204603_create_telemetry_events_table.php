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
        Schema::create('telemetry_events', function (Blueprint$table) {
            $table->bigIncrements('id');

            $table->string('event_name',64);// event_view, first_scroll, answer_submit, answer_submit_failed
            $table->foreignId('form_id')->nullable()->constrained('forms')->nullOnDelete();

            $table->uuid('view_id')->nullable();
            $table->char('participant_token_hash',64)->nullable();

            $table->timestamp('server_ts');
            $table->timestamp('client_ts')->nullable();

            $table->string('device_type',16)->nullable();// mobile/desktop
            $table->string('locale',16)->nullable();
            $table->string('user_agent',255)->nullable();

            $table->json('properties')->nullable();// intent, error_type, scroll_depth など

            $table->timestamps();

            $table->index(['event_name','server_ts']);
            $table->index(['form_id','event_name','server_ts']);
            $table->index(['view_id']);
            $table->index(['participant_token_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetry_events');
    }
};
