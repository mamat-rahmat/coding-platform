<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $enrollments = DB::table('lesson_progress')
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('course_modules', 'course_modules.id', '=', 'lessons.course_module_id')
            ->select('lesson_progress.user_id', 'course_modules.course_id')
            ->distinct()
            ->get();

        foreach ($enrollments as $e) {
            DB::table('course_user')->updateOrInsert(
                ['user_id' => $e->user_id, 'course_id' => $e->course_id],
                ['created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        //
    }
};
