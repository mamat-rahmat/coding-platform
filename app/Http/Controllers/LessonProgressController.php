<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    public function complete(
        Request $request,
        Lesson $lesson
    ): RedirectResponse {
        abort_unless($lesson->is_published, 404);

        if (! $lesson->isUnlockedFor($request->user())) {
            return redirect()
                ->route('courses.show', $lesson->module->course->slug)
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Selesaikan lesson sebelumnya untuk membuka lesson ini.',
                ]);
        }

        LessonProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'completed_at' => now(),
            ]
        );

        return back();
    }
}
