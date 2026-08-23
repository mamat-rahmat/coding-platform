<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $gradedTypes = [
            'MCQ_SINGLE',
            'MCQ_MULTIPLE',
            'CODE_FILL',
            'CODE_REORDER',
            'CODE_CHALLENGE',
        ];

        $incompleteLessons = DB::table('lesson_progress')
            ->whereNull('completed_at')
            ->get();

        $fixed = 0;

        foreach ($incompleteLessons as $progress) {
            $gradedBlockIds = DB::table('lesson_blocks')
                ->where('lesson_id', $progress->lesson_id)
                ->whereIn('type', $gradedTypes)
                ->pluck('id');

            if ($gradedBlockIds->isEmpty()) {
                continue;
            }

            $correctBlockIds = DB::table('block_attempts')
                ->where('user_id', $progress->user_id)
                ->whereIn('lesson_block_id', $gradedBlockIds)
                ->where('is_correct', true)
                ->pluck('lesson_block_id')
                ->unique();

            if ($correctBlockIds->count() === $gradedBlockIds->count()) {
                DB::table('lesson_progress')
                    ->where('user_id', $progress->user_id)
                    ->where('lesson_id', $progress->lesson_id)
                    ->update(['completed_at' => $progress->created_at]);

                $fixed++;
            }
        }
    }

    public function down(): void
    {
        //
    }
};
