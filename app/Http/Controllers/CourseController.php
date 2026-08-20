<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->withCount('modules')
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'slug',
                'description',
                'language',
                'level',
                'thumbnail',
                'xp_reward',
            ]);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, Course $course): Response
    {
        abort_unless($course->is_published, 404);

        $course->load([
            'modules' => fn ($query) => $query
                ->orderBy('sort_order')
                ->with([
                    'lessons' => fn ($query) => $query
                        ->where('is_published', true)
                        ->orderBy('sort_order')
                        ->select([
                            'id',
                            'course_module_id',
                            'title',
                            'slug',
                            'description',
                            'sort_order',
                        ]),
                ]),
        ]);

        $course->load([
            'modules.lessons',
        ]);

        $lessons = $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->sortBy('sort_order')
            ->values();

        $completedLessonIds = $request->user()
            ->lessonProgresses()
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        $course->modules->each(function ($module) use ($completedLessonIds) {
            $module->lessons->each(function ($lesson) use ($completedLessonIds) {
                $lesson->is_completed = $completedLessonIds->contains($lesson->id);
            });
        });

        $previousCompleted = true;

        $lessons->each(function ($lesson) use (&$previousCompleted, $completedLessonIds) {
            $lesson->is_locked = ! $previousCompleted;
            $previousCompleted = $completedLessonIds->contains($lesson->id);
        });

        $totalLessons = $lessons->count();

        $completedLessons = $lessons
            ->whereIn('id', $completedLessonIds)
            ->count();

        $progressPercentage = $totalLessons > 0
            ? (int) round(($completedLessons / $totalLessons) * 100)
            : 0;

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'lessons' => $lessons,
            'progress' => [
                'totalLessons' => $totalLessons,
                'completedLessons' => $completedLessons,
                'percentage' => $progressPercentage,
            ],
        ]);
    }

    public function leaderboard(Request $request, Course $course): Response
    {
        abort_unless($course->is_published, 404);

        $lessonIds = $course->lessons()
            ->where('is_published', true)
            ->pluck('lessons.id');

        $totalLessons = $lessonIds->count();

        $participants = LessonProgress::query()
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->selectRaw(
                'user_id, count(*) as completed_count, max(completed_at) as last_completed_at',
            )
            ->groupBy('user_id')
            ->with('user:id,name')
            ->orderByDesc('completed_count')
            ->orderBy('last_completed_at')
            ->get();

        $currentUserId = $request->user()->id;

        $leaderboard = $participants->map(function ($participant, int $index) use ($totalLessons, $course, $currentUserId) {
            $percentage = $totalLessons > 0
                ? (int) round(($participant->completed_count / $totalLessons) * 100)
                : 0;

            return [
                'rank' => $index + 1,
                'name' => $participant->user->name,
                'completed_lessons' => $participant->completed_count,
                'total_lessons' => $totalLessons,
                'percentage' => $percentage,
                'xp' => round($course->xp_reward * ($percentage / 100)),
                'is_current_user' => $participant->user_id === $currentUserId,
            ];
        });

        $currentUserEntry = $leaderboard->firstWhere('is_current_user', true);

        return Inertia::render('Courses/Leaderboard', [
            'course' => $course->only(['id', 'title', 'slug']),
            'leaderboard' => $leaderboard->values(),
            'currentUserRank' => $currentUserEntry['rank'] ?? null,
        ]);
    }
}
