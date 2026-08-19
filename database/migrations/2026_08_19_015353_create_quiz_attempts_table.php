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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('lesson_block_id')
                ->constrained('lesson_blocks')
                ->cascadeOnDelete();
            $table->string('selected_answer', 10);
            $table->boolean('is_correct');
            $table->timestamp('answered_at');
            $table->timestamps();
            $table->index(['user_id', 'lesson_block_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
