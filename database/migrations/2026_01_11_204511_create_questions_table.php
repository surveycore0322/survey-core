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
        Schema::create('questions', function (Blueprint$table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();

            $table->string('label');
            $table->string('type');// boolean, select, multi, text, number ...
            $table->string('purpose', 50)->nullable()->index();
            $table->json('options')->nullable();
            $table->boolean('required')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['form_id','sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
