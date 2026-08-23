<?php

namespace App\Http\Controllers;

use App\LessonBlockType;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function show(Request $request, Lesson $lesson): Response|RedirectResponse
    {
        abort_unless($lesson->is_published, 404);

        if (! $lesson->isUnlockedFor($request->user())) {
            $message = $lesson->module->isUnlockedFor($request->user())
                ? 'Selesaikan lesson sebelumnya untuk membuka lesson ini.'
                : 'Selesaikan semua pelajaran di modul sebelumnya untuk membuka modul ini.';

            return redirect()
                ->route('courses.show', $lesson->module->course->slug)
                ->with('toast', [
                    'type' => 'error',
                    'message' => $message,
                ]);
        }

        $lesson->load([
            'module.course',
            'blocks' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $orderedLessons = $lesson->orderedInCourse();

        $position = $orderedLessons->search(
            fn (Lesson $ordered) => $ordered->id === $lesson->id,
        );

        $previousLesson = $position === false || $position === 0
            ? null
            : $orderedLessons[$position - 1];

        $nextLesson = $position !== false && $position < $orderedLessons->count() - 1
            ? $orderedLessons[$position + 1]
            : null;

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
            $block->selected_answer = $attempt?->selected_answer;
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
