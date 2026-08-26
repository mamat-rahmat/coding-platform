<?php

namespace App\Http\Controllers;

use App\LessonBlockType;
use App\Models\BlockAttempt;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockAttemptController extends Controller
{
    public function store(
        Request $request,
        LessonBlock $lessonBlock,
    ): RedirectResponse {
        $type = $lessonBlock->type;

        $accessTypes = [
            LessonBlockType::TEXT,
            LessonBlockType::HINT,
            LessonBlockType::CODE_EXAMPLE,
        ];

        $gradedTypes = [
            LessonBlockType::MCQ_SINGLE,
            LessonBlockType::MCQ_MULTIPLE,
            LessonBlockType::CODE_FILL,
            LessonBlockType::CODE_REORDER,
            LessonBlockType::CODE_CHALLENGE,
        ];

        abort_unless(
            in_array($type, [...$gradedTypes, ...$accessTypes], true),
            404,
        );

        $lesson = Lesson::query()->findOrFail($lessonBlock->lesson_id);

        abort_unless($lesson->is_published, 404);

        abort_unless($lesson->isUnlockedFor($request->user()), 403);

        $existingAttempt = $request->user()
            ->blockAttempts()
            ->where('lesson_block_id', $lessonBlock->id)
            ->first();

        if (in_array($type, $accessTypes, true)) {
            if ($existingAttempt) {
                return back();
            }

            BlockAttempt::create([
                'user_id' => $request->user()->id,
                'lesson_block_id' => $lessonBlock->id,
                'selected_answer' => '',
                'is_correct' => true,
                'answered_at' => now(),
            ]);

            return back();
        }

        if ($existingAttempt && $existingAttempt->is_correct) {
            return back();
        }

        $payload = match ($type) {
            LessonBlockType::MCQ_SINGLE => $this->verifyMcqSingle($request, $lessonBlock),
            LessonBlockType::MCQ_MULTIPLE => $this->verifyMcqMultiple($request, $lessonBlock),
            LessonBlockType::CODE_FILL => $this->verifyCodeFill($request, $lessonBlock),
            LessonBlockType::CODE_REORDER => $this->verifyCodeReorder($request, $lessonBlock),
            LessonBlockType::CODE_CHALLENGE => $this->verifyCodeChallenge($request, $lessonBlock),
        };

        if ($existingAttempt) {
            $existingAttempt->update([
                'selected_answer' => $payload['answer'],
                'is_correct' => $payload['is_correct'],
                'attempt_data' => $payload['attempt_data'] ?? null,
                'score' => $payload['score'] ?? null,
                'answered_at' => now(),
            ]);
        } else {
            BlockAttempt::create([
                'user_id' => $request->user()->id,
                'lesson_block_id' => $lessonBlock->id,
                'selected_answer' => $payload['answer'],
                'is_correct' => $payload['is_correct'],
                'attempt_data' => $payload['attempt_data'] ?? null,
                'score' => $payload['score'] ?? null,
                'answered_at' => now(),
            ]);
        }

        if ($payload['is_correct']) {
            $this->maybeCompleteLesson($request, $lessonBlock);
        }

        return back()->with('attempt_result', [
            'block_id' => $lessonBlock->id,
            'selected_answer' => $payload['answer'],
            'is_correct' => $payload['is_correct'],
        ]);
    }

    /**
     * Mark the lesson complete if every graded block in it
     * has at least one correct attempt by the user.
     */
    private function maybeCompleteLesson(Request $request, LessonBlock $block): void
    {
        $lesson = $block->lesson()->first();

        if (! $lesson) {
            return;
        }

        $gradedTypes = [
            LessonBlockType::MCQ_SINGLE,
            LessonBlockType::MCQ_MULTIPLE,
            LessonBlockType::CODE_FILL,
            LessonBlockType::CODE_REORDER,
            LessonBlockType::CODE_CHALLENGE,
        ];

        $gradedBlockIds = $lesson->blocks()
            ->whereIn('type', $gradedTypes)
            ->pluck('id');

        if ($gradedBlockIds->isEmpty()) {
            LessonProgress::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'completed_at' => now(),
                ],
            );

            return;
        }

        $attemptedBlockIds = $request->user()
            ->blockAttempts()
            ->whereIn('lesson_block_id', $gradedBlockIds)
            ->where('is_correct', true)
            ->pluck('lesson_block_id')
            ->unique();

        if ($attemptedBlockIds->count() !== $gradedBlockIds->count()) {
            return;
        }

        LessonProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'completed_at' => now(),
            ],
        );
    }

    /**
     * @return array{answer: string, is_correct: bool}
     */
    private function verifyMcqSingle(Request $request, LessonBlock $block): array
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:10'],
        ]);

        $correct = $block->content['correct_answer'] ?? null;
        abort_unless($correct !== null, 422);

        return [
            'answer' => $validated['answer'],
            'is_correct' => $validated['answer'] === $correct,
        ];
    }

    /**
     * @return array{answer: string, is_correct: bool}
     */
    private function verifyMcqMultiple(Request $request, LessonBlock $block): array
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:255'],
        ]);

        $correctAnswers = $block->content['correct_answers'] ?? [];
        abort_unless(is_array($correctAnswers) && count($correctAnswers) > 0, 422);

        $selected = collect(explode(',', $validated['answer']))
            ->sort()
            ->values()
            ->implode(',');

        $correctSorted = implode(
            ',',
            array_map(fn ($value): string => (string) $value, $correctAnswers),
        );

        return [
            'answer' => $validated['answer'],
            'is_correct' => $selected === $correctSorted,
        ];
    }

    /**
     * @return array{answer: string, is_correct: bool}
     */
    private function verifyCodeFill(Request $request, LessonBlock $block): array
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:1000'],
            'is_correct' => ['boolean'],
        ]);

        return [
            'answer' => $validated['answer'],
            'is_correct' => (bool) ($validated['is_correct'] ?? false),
        ];
    }

    /**
     * @return array{answer: string, is_correct: bool}
     */
    private function verifyCodeReorder(Request $request, LessonBlock $block): array
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:255'],
        ]);

        $correctOrder = $block->content['correct_order'] ?? [];
        abort_unless(is_array($correctOrder), 422);

        $selected = array_map(
            'intval',
            explode(',', $validated['answer']),
        );

        return [
            'answer' => $validated['answer'],
            'is_correct' => $selected === $correctOrder,
        ];
    }

    /**
     * @return array{answer: string, is_correct: bool, attempt_data: ?array<int, mixed>, score: ?int}
     */
    private function verifyCodeChallenge(Request $request, LessonBlock $block): array
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:50'],
            'is_correct' => ['required', 'boolean'],
            'attempt_data' => ['nullable', 'array'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        return [
            'answer' => $validated['answer'],
            'is_correct' => (bool) $validated['is_correct'],
            'attempt_data' => $validated['attempt_data'] ?? null,
            'score' => $validated['score'] ?? null,
        ];
    }
}
