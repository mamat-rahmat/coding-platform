<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('lesson_blocks')
            ->where('type', 'QUIZ')
            ->update(['type' => 'MCQ_SINGLE']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('lesson_blocks')
            ->where('type', 'MCQ_SINGLE')
            ->update(['type' => 'QUIZ']);
    }
};
