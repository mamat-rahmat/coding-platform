<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlockAttempt;
use App\Models\LessonBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlockAttemptController extends Controller
{
    public function store(
        Request $request,
        LessonBlock $lessonBlock,
    ): RedirectResponse {
        abort_unless($lessonBlock->type->value === 'MCQ_SINGLE', 404);

        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:255'],
        ]);

        $correctAnswer = $lessonBlock->content['correct_answer'] ?? null;

        abort_unless($correctAnswer !== null, 422);

        $isCorrect = $validated['answer'] === $correctAnswer;

        BlockAttempt::create([
            'user_id' => $request->user()->id,
            'lesson_block_id' => $lessonBlock->id,
            'selected_answer' => $validated['answer'],
            'is_correct' => $isCorrect,
            'answered_at' => now(),
        ]);

        return back()->with('attempt_result', [
            'block_id' => $lessonBlock->id,
            'selected_answer' => $validated['answer'],
            'is_correct' => $isCorrect,
        ]);
    }
}
