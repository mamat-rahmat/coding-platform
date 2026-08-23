<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

            $module->is_completed = $module->lessons->isNotEmpty()
                && $module->lessons->every(fn ($lesson) => $lesson->is_completed);
        });

        $moduleBlocked = false;

        $course->modules->each(function ($module) use (&$moduleBlocked, $completedLessonIds) {
            $module->is_locked = $moduleBlocked;

            $previousInModule = true;

            $module->lessons->each(function ($lesson) use (&$previousInModule, $completedLessonIds, $module) {
                $lesson->is_locked = $module->is_locked || ! $previousInModule;
                $previousInModule = $completedLessonIds->contains($lesson->id);
            });

            if (! $module->is_completed) {
                $moduleBlocked = true;
            }
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

        $participants = $course->users()
            ->where('is_admin', false)
            ->withCount([
                'lessonProgresses' => fn ($query) => $query
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereNotNull('completed_at'),
            ])
            ->with('lessonProgresses', fn ($query) => $query
                ->whereIn('lesson_id', $lessonIds)
                ->whereNotNull('completed_at')
                ->selectRaw('user_id, max(completed_at) as last_completed_at')
                ->groupBy('user_id'))
            ->orderByDesc('lesson_progresses_count')
            ->get(['users.id', 'users.uuid', 'users.name']);

        $currentUserId = $request->user()?->id ?? null;

        $leaderboard = $participants->map(function ($participant, int $index) use ($totalLessons, $course, $currentUserId) {
            $completedCount = $participant->lesson_progresses_count;
            $lastCompleted = $participant->relationLoaded('lessonProgresses')
                ? $participant->lessonProgresses->first()?->last_completed_at
                : null;
            $percentage = $totalLessons > 0
                ? (int) round(($completedCount / $totalLessons) * 100)
                : 0;

            return [
                'rank' => $index + 1,
                'user_id' => $participant->id,
                'uuid' => $participant->uuid,
                'name' => $participant->name,
                'completed_lessons' => $completedCount,
                'total_lessons' => $totalLessons,
                'percentage' => $percentage,
                'xp' => round($course->xp_reward * ($percentage / 100)),
                'is_current_user' => $participant->id === $currentUserId,
            ];
        });

        $currentUserEntry = $leaderboard->firstWhere('is_current_user', true);

        return Inertia::render('Courses/Leaderboard', [
            'course' => $course->only(['id', 'title', 'slug']),
            'leaderboard' => $leaderboard->values(),
            'currentUserRank' => $currentUserEntry['rank'] ?? null,
        ]);
    }

    public function leaderboardUserProgress(Course $course, User $user): JsonResponse
    {
        abort_unless($course->is_published, 404);

        $publishedLessonIds = $course->lessons()->where('is_published', true)->pluck('lessons.id');

        $completedLessonIds = $user->lessonProgresses()
            ->whereIn('lesson_id', $publishedLessonIds)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        $correctBlockIds = $user->blockAttempts()
            ->where('is_correct', true)
            ->whereIn('lesson_block_id', fn ($query) => $query
                ->select('id')
                ->from('lesson_blocks')
                ->whereIn('lesson_id', $publishedLessonIds))
            ->pluck('lesson_block_id');

        $modules = $course->modules()
            ->orderBy('sort_order')
            ->with([
                'lessons' => fn ($query) => $query
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->with('blocks:id,lesson_id,type,sort_order')
                    ->select(['id', 'course_module_id', 'title', 'sort_order']),
            ])
            ->get(['id', 'course_id', 'title', 'sort_order']);

        $data = $modules->map(function ($module) use ($completedLessonIds, $correctBlockIds) {
            return [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $module->lessons->sortBy('sort_order')->values()->map(function ($lesson) use ($completedLessonIds, $correctBlockIds) {
                    $totalBlocks = $lesson->blocks->count();
                    $completedBlocks = $lesson->blocks
                        ->filter(fn ($block) => $correctBlockIds->contains($block->id))
                        ->count();

                    $blocks = $lesson->blocks->sortBy('sort_order')->values()->map(function ($block) use ($correctBlockIds) {
                        return [
                            'id' => $block->id,
                            'type' => $block->type->value,
                            'sort_order' => $block->sort_order,
                            'is_completed' => $correctBlockIds->contains($block->id),
                        ];
                    });

                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'sort_order' => $lesson->sort_order,
                        'is_completed' => $completedLessonIds->contains($lesson->id),
                        'blocks_completed' => $completedBlocks,
                        'blocks_total' => $totalBlocks,
                        'blocks' => $blocks,
                    ];
                }),
            ];
        });

        return response()->json([
            'user' => ['id' => $user->id, 'name' => $user->name],
            'modules' => $data,
        ]);
    }
}
