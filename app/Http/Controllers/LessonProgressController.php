<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
