<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function show(Lesson $lesson): Response
    {
        abort_unless($lesson->is_published, 404);

        $lesson->load([
            'module.course',
            'blocks' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        return Inertia::render('Lessons/Show', [
            'lesson' => $lesson,
        ]);
    }
}
