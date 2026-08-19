<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LessonBlock;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    public function store(
        Request $request,
        LessonBlock $lessonBlock,
    ): RedirectResponse {
        abort_unless($lessonBlock->type->value === 'MCQ_SINGLE', 404);

        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:10'],
        ]);

        $correctAnswer = $lessonBlock->content['correct_answer'] ?? null;

        abort_unless($correctAnswer !== null, 422);

        $isCorrect = $validated['answer'] === $correctAnswer;

        QuizAttempt::create([
            'user_id' => $request->user()->id,
            'lesson_block_id' => $lessonBlock->id,
            'selected_answer' => $validated['answer'],
            'is_correct' => $isCorrect,
            'answered_at' => now(),
        ]);

        return back()->with('quiz_result', [
            'block_id' => $lessonBlock->id,
            'selected_answer' => $validated['answer'],
            'is_correct' => $isCorrect,
        ]);
    }
}
