<?php

namespace App\Http\Controllers;

use App\LessonBlockType;
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

        $gradedTypes = [
            LessonBlockType::MCQ_SINGLE,
            LessonBlockType::MCQ_MULTIPLE,
            LessonBlockType::CODE_FILL,
            LessonBlockType::CODE_REORDER,
            LessonBlockType::CODE_CHALLENGE,
        ];

        $gradedBlockIds = $lesson->blocks
            ->filter(fn ($block) => in_array($block->type, $gradedTypes, true))
            ->pluck('id')
            ->all();

        $latestAttempts = $request->user()
            ->blockAttempts()
            ->whereIn('lesson_block_id', $gradedBlockIds)
            ->orderByDesc('answered_at')
            ->get()
            ->keyBy('lesson_block_id');

        $lesson->blocks->each(function ($block) use ($latestAttempts) {
            $attempt = $latestAttempts->get($block->id);

            $block->is_answered = $attempt !== null;
            $block->is_correct = $attempt?->is_correct;
        });

        $totalGraded = count($gradedBlockIds);
        $correctGraded = $latestAttempts
            ->filter(fn ($attempt) => $attempt->is_correct)
            ->count();

        $allGradedCorrect = $totalGraded > 0
            && $correctGraded === $totalGraded;

        return Inertia::render('Lessons/Show', [
            'lesson' => $lesson,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'isCompleted' => $isCompleted,
            'blockStatus' => [
                'totalGraded' => $totalGraded,
                'correctGraded' => $correctGraded,
                'allCorrect' => $allGradedCorrect,
            ],
        ]);
    }
}
