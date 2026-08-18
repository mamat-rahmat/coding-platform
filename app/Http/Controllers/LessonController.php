<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function show(Request $request, Lesson $lesson): Response
    {
        abort_unless($lesson->is_published, 404);

        $lesson->load([
            'module.course',
            'blocks' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $previousLesson = Lesson::query()
            ->where('course_module_id', $lesson->course_module_id)
            ->where('sort_order', '<', $lesson->sort_order)
            ->where('is_published', true)
            ->orderByDesc('sort_order')
            ->first();

        $nextLesson = Lesson::query()
            ->where('course_module_id', $lesson->course_module_id)
            ->where('sort_order', '>', $lesson->sort_order)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->first();

        $isCompleted = $request->user()
            ->lessonProgresses()
            ->where('lesson_id', $lesson->id)
            ->whereNotNull('completed_at')
            ->exists();

        return Inertia::render('Lessons/Show', [
            'lesson' => $lesson,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'isCompleted' => $isCompleted,
        ]);
    }
}
