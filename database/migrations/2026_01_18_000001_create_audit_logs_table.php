<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // "occurred_at" を正とする（created_at依存を避ける）
            $table->dateTime('occurred_at', 3)->index();

            $table->string('trace_id', 64)->index();
            $table->string('level', 16)->index();          // info/warn/error
            $table->string('event', 128)->index();         // e.g. http.request, snapshot.submitted
            $table->string('scope_type', 32)->index();     // form/snapshot/participant/system...
            $table->string('scope_id', 64)->nullable()->index();

            $table->string('actor_type', 32)->nullable()->index(); // participant/host/system
            $table->string('actor_id', 64)->nullable()->index();

            $table->json('request_json')->nullable();
            $table->json('io_json')->nullable();
            $table->json('details_json')->nullable();

            // まずは保持のため残す（運用で不要なら後で落とす）
            $table->timestamps();

            $table->index(['event', 'occurred_at']);
            $table->index(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
