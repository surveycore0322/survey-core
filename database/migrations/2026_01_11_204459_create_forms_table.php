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
        Schema::create('forms', function (Blueprint$table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('type');// attend, survey, vote, memories etc
            $table->dateTime('event_date')->nullable();

            $table->timestamps();

            $table->index(['type','event_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
