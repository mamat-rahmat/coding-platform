<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\LessonBlock;
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
                            'is_optional',
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

            $requiredLessons = $module->lessons->where('is_optional', false);
            $module->is_completed = $requiredLessons->isNotEmpty()
                && $requiredLessons->every(fn ($lesson) => $lesson->is_completed);
        });

        $moduleBlocked = false;

        $course->modules->each(function ($module) use (&$moduleBlocked, $completedLessonIds) {
            $module->is_locked = $moduleBlocked;

            $previousRequired = true;

            $module->lessons->each(function ($lesson) use (&$previousRequired, $completedLessonIds, $module) {
                $lesson->is_locked = $module->is_locked || ! $previousRequired;

                if (! $lesson->is_optional) {
                    $previousRequired = $completedLessonIds->contains($lesson->id);
                }
            });

            if (! $module->is_completed) {
                $moduleBlocked = true;
            }
        });

        $requiredLessons = $lessons->where('is_optional', false);
        $totalLessons = $requiredLessons->count();

        $completedLessons = $requiredLessons
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

    public function syllabus(Request $request, Course $course): Response
    {
        abort_unless($course->is_published, 404);

        $publishedLessonIds = $course->lessons()
            ->where('is_published', true)
            ->pluck('lessons.id');

        $completedLessonIds = $request->user()
            ->lessonProgresses()
            ->whereIn('lesson_id', $publishedLessonIds)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        $correctBlockIds = $request->user()
            ->blockAttempts()
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
                    ->with('blocks:id,lesson_id,type,title,sort_order')
                    ->select([
                        'id',
                        'course_module_id',
                        'title',
                        'slug',
                        'sort_order',
                        'is_optional',
                    ]),
            ])
            ->get(['id', 'course_id', 'title', 'sort_order']);

        $data = $modules->map(function (CourseModule $module) use ($completedLessonIds, $correctBlockIds) {
            $lessonsData = [];

            foreach ($module->lessons->sortBy('sort_order') as $lesson) {
                $blocksData = [];

                foreach ($lesson->blocks->sortBy('sort_order') as $block) {
                    $blocksData[] = [
                        'id' => $block->id,
                        'type' => $block->type->value,
                        'title' => $block->title,
                        'sort_order' => $block->sort_order,
                        'is_completed' => $correctBlockIds->contains($block->id),
                    ];
                }

                $completedBlocks = $lesson->blocks
                    ->filter(fn (LessonBlock $block) => $correctBlockIds->contains($block->id))
                    ->count();

                $lessonsData[] = [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'slug' => $lesson->slug,
                    'sort_order' => $lesson->sort_order,
                    'is_optional' => $lesson->is_optional,
                    'is_completed' => $completedLessonIds->contains($lesson->id),
                    'blocks_completed' => $completedBlocks,
                    'blocks_total' => $lesson->blocks->count(),
                    'blocks' => $blocksData,
                ];
            }

            return [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $lessonsData,
            ];
        });

        return Inertia::render('Courses/Syllabus', [
            'course' => $course->only([
                'id',
                'title',
                'slug',
                'description',
                'language',
                'level',
                'xp_reward',
            ]),
            'modules' => $data->values(),
        ]);
    }

    public function leaderboard(Request $request, Course $course): Response
    {
        abort_unless($course->is_published, 404);

        $lessonIds = $course->lessons()
            ->where('is_published', true)
            ->pluck('lessons.id');

        $totalLessons = $lessonIds->count();

        $participants = User::query()
            ->where('is_admin', false)
            ->whereHas('courses', fn ($query) => $query->whereKey($course->id))
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

        $currentUserId = $request->user()?->id;

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
                    ->with('blocks:id,lesson_id,type,title,sort_order')
                    ->select(['id', 'course_module_id', 'title', 'sort_order']),
            ])
            ->get(['id', 'course_id', 'title', 'sort_order']);

        $data = $modules->map(function (CourseModule $module) use ($completedLessonIds, $correctBlockIds): array {
            $lessonsData = [];

            foreach ($module->lessons->sortBy('sort_order') as $lesson) {
                $blocksData = [];

                foreach ($lesson->blocks->sortBy('sort_order') as $block) {
                    $blocksData[] = [
                        'id' => $block->id,
                        'type' => $block->type->value,
                        'title' => $block->title,
                        'sort_order' => $block->sort_order,
                        'is_completed' => $correctBlockIds->contains($block->id),
                    ];
                }

                $totalBlocks = $lesson->blocks->count();
                $completedBlocks = $lesson->blocks
                    ->filter(fn (LessonBlock $block) => $correctBlockIds->contains($block->id))
                    ->count();

                $lessonsData[] = [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'sort_order' => $lesson->sort_order,
                    'is_completed' => $completedLessonIds->contains($lesson->id),
                    'blocks_completed' => $completedBlocks,
                    'blocks_total' => $totalBlocks,
                    'blocks' => $blocksData,
                ];
            }

            return [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $lessonsData,
            ];
        });

        return response()->json([
            'user' => ['id' => $user->id, 'name' => $user->name],
            'modules' => $data,
        ]);
    }
}
