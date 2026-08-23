<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(User $user): Response
    {
        $courseIds = $user->lessonProgresses()
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->unique();

        $courses = Course::query()
            ->where('is_published', true)
            ->whereHas('lessons', fn ($q) => $q->whereIn('lessons.id', $courseIds))
            ->withCount(['lessons as total_lessons' => fn ($q) => $q->where('is_published', true)])
            ->with(['modules' => fn ($q) => $q->orderBy('sort_order')
                ->with(['lessons' => fn ($q) => $q->where('is_published', true)
                    ->select(['id', 'course_module_id', 'sort_order']),
                ])
                ->select(['id', 'course_id', 'sort_order']),
            ])
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'description', 'language', 'level', 'xp_reward']);

        $courseIdsInProgress = $user->lessonProgresses()
            ->pluck('lesson_id')
            ->unique();

        $coursesInProgress = Course::query()
            ->where('is_published', true)
            ->whereHas('lessons', fn ($q) => $q->whereIn('lessons.id', $courseIdsInProgress))
            ->whereNotIn('id', $courses->pluck('id'))
            ->withCount(['lessons as total_lessons' => fn ($q) => $q->where('is_published', true)])
            ->with(['modules' => fn ($q) => $q->orderBy('sort_order')
                ->with(['lessons' => fn ($q) => $q->where('is_published', true)
                    ->select(['id', 'course_module_id', 'sort_order']),
                ])
                ->select(['id', 'course_id', 'sort_order']),
            ])
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'description', 'language', 'level', 'xp_reward']);

        $allCourses = $courses->concat($coursesInProgress)->unique('id')->values();

        $data = $allCourses->map(function ($course) use ($user) {
            $allLessonIds = $course->modules->flatMap->lessons->pluck('id');
            $completedCount = $user->lessonProgresses()
                ->whereIn('lesson_id', $allLessonIds)
                ->whereNotNull('completed_at')
                ->count();
            $total = $allLessonIds->count();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'language' => $course->language,
                'level' => $course->level,
                'xp_reward' => $course->xp_reward,
                'total_lessons' => $total,
                'completed_lessons' => $completedCount,
                'percentage' => $total > 0 ? (int) round(($completedCount / $total) * 100) : 0,
            ];
        });

        $totalXp = $user->blockAttempts()
            ->where('is_correct', true)
            ->sum('score');

        return Inertia::render('Users/Show', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'created_at' => $user->created_at->toISOString(),
            ],
            'courses' => $data,
            'totalXp' => (int) $totalXp,
        ]);
    }

    public function userCourseProgress(User $user, Course $course): JsonResponse
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
                    $blocks = $lesson->blocks->sortBy('sort_order')->values()->map(function ($block) use ($correctBlockIds) {
                        return [
                            'id' => $block->id,
                            'type' => $block->type->value,
                            'sort_order' => $block->sort_order,
                            'is_completed' => $correctBlockIds->contains($block->id),
                        ];
                    });

                    $totalBlocks = $blocks->count();
                    $completedBlocks = $blocks->filter->is_completed->count();

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

        return response()->json(['modules' => $data]);
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        $courseIds = $user->lessonProgresses()
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->unique();

        $courses = Course::query()
            ->where('is_published', true)
            ->whereHas('lessons', fn ($q) => $q->whereIn('lessons.id', $courseIds))
            ->withCount(['lessons as total_lessons' => fn ($q) => $q->where('is_published', true)])
            ->with(['modules' => fn ($q) => $q->orderBy('sort_order')
                ->with(['lessons' => fn ($q) => $q->where('is_published', true)
                    ->select(['id', 'course_module_id', 'sort_order']),
                ])
                ->select(['id', 'course_id', 'sort_order']),
            ])
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'description', 'language', 'level', 'xp_reward']);

        $courseIdsInProgress = $user->lessonProgresses()
            ->pluck('lesson_id')
            ->unique();

        $coursesInProgress = Course::query()
            ->where('is_published', true)
            ->whereHas('lessons', fn ($q) => $q->whereIn('lessons.id', $courseIdsInProgress))
            ->whereNotIn('id', $courses->pluck('id'))
            ->withCount(['lessons as total_lessons' => fn ($q) => $q->where('is_published', true)])
            ->with(['modules' => fn ($q) => $q->orderBy('sort_order')
                ->with(['lessons' => fn ($q) => $q->where('is_published', true)
                    ->select(['id', 'course_module_id', 'sort_order']),
                ])
                ->select(['id', 'course_id', 'sort_order']),
            ])
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'description', 'language', 'level', 'xp_reward']);

        $allCourses = $courses->concat($coursesInProgress)->unique('id')->values();

        $data = $allCourses->map(function ($course) use ($user) {
            $allLessonIds = $course->modules->flatMap->lessons->pluck('id');
            $completedCount = $user->lessonProgresses()
                ->whereIn('lesson_id', $allLessonIds)
                ->whereNotNull('completed_at')
                ->count();
            $total = $allLessonIds->count();

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'language' => $course->language,
                'level' => $course->level,
                'xp_reward' => $course->xp_reward,
                'total_lessons' => $total,
                'completed_lessons' => $completedCount,
                'percentage' => $total > 0 ? (int) round(($completedCount / $total) * 100) : 0,
            ];
        });

        return Inertia::render('Profile/Index', [
            'courses' => $data,
        ]);
    }

    public function courseProgress(Course $course): Response
    {
        $user = request()->user();

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

        $totalLessons = $publishedLessonIds->count();
        $completedLessons = $completedLessonIds->count();

        $data = $modules->map(function ($module) use ($completedLessonIds, $correctBlockIds) {
            return [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $module->lessons->sortBy('sort_order')->values()->map(function ($lesson) use ($completedLessonIds, $correctBlockIds) {
                    $blocks = $lesson->blocks->sortBy('sort_order')->values()->map(function ($block) use ($correctBlockIds) {
                        return [
                            'id' => $block->id,
                            'type' => $block->type->value,
                            'sort_order' => $block->sort_order,
                            'is_completed' => $correctBlockIds->contains($block->id),
                        ];
                    });

                    $totalBlocks = $blocks->count();
                    $completedBlocks = $blocks->filter->is_completed->count();

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

        return Inertia::render('Profile/CourseProgress', [
            'course' => $course->only(['id', 'title', 'slug']),
            'modules' => $data,
            'progress' => [
                'totalLessons' => $totalLessons,
                'completedLessons' => $completedLessons,
                'percentage' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
            ],
        ]);
    }
}
