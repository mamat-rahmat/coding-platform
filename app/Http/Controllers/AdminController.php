<?php

namespace App\Http\Controllers;

use App\LessonBlockType;
use App\Models\BlockAttempt;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function dashboard(): Response
    {
        $blockTypeCounts = [];
        foreach (LessonBlockType::cases() as $type) {
            $blockTypeCounts[$type->value] = LessonBlock::where('type', $type)->count();
        }

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'courses' => Course::count(),
                'publishedCourses' => Course::where('is_published', true)->count(),
                'lessons' => Lesson::count(),
                'publishedLessons' => Lesson::where('is_published', true)->count(),
                'blocks' => LessonBlock::count(),
                'blockTypeCounts' => $blockTypeCounts,
                'users' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
                'attempts' => BlockAttempt::count(),
                'correctAttempts' => BlockAttempt::where('is_correct', true)->count(),
            ],
        ]);
    }
}
