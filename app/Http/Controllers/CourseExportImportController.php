<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseExportImportController extends Controller
{
    public function export(Course $course): StreamedResponse
    {
        $this->authorize('view', $course);

        $course->load([
            'modules' => fn ($q) => $q->orderBy('sort_order'),
            'modules.lessons' => fn ($q) => $q->orderBy('sort_order'),
            'modules.lessons.blocks' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $data = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'course' => [
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'language' => $course->language,
                'level' => $course->level,
                'xp_reward' => $course->xp_reward,
                'is_published' => $course->is_published,
                'modules' => $course->modules->map(fn ($module) => [
                    'title' => $module->title,
                    'slug' => $module->slug,
                    'description' => $module->description,
                    'sort_order' => $module->sort_order,
                    'lessons' => $module->lessons->map(fn ($lesson) => [
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'description' => $lesson->description,
                        'sort_order' => $lesson->sort_order,
                        'is_published' => $lesson->is_published,
                        'is_optional' => $lesson->is_optional,
                        'blocks' => $lesson->blocks->map(fn ($block) => [
                            'type' => $block->type->value,
                            'title' => $block->title,
                            'content' => $block->content,
                            'sort_order' => $block->sort_order,
                        ])->toArray(),
                    ])->toArray(),
                ])->toArray(),
            ],
        ];

        $filename = 'course-'.$course->slug.'-'.now()->format('Y-m-d-His').'.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function import(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:json', 'max:10240'],
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            abort(422, 'File JSON tidak valid.');
        }

        if (! isset($data['course'], $data['version'])) {
            abort(422, 'Format export tidak valid. File harus berisi data course dengan version.');
        }

        $courseData = $data['course'];

        $course = \DB::transaction(function () use ($courseData) {
            $course = Course::create([
                'title' => $courseData['title'] ?? 'Imported Course',
                'slug' => $this->uniqueSlug($courseData['slug'] ?? 'imported-course', Course::class),
                'description' => $courseData['description'] ?? null,
                'language' => $courseData['language'] ?? 'python',
                'level' => $courseData['level'] ?? 'beginner',
                'xp_reward' => $courseData['xp_reward'] ?? 500,
                'is_published' => false,
            ]);

            foreach ($courseData['modules'] ?? [] as $moduleData) {
                $module = $course->modules()->create([
                    'title' => $moduleData['title'] ?? 'Module',
                    'slug' => $this->uniqueSlug($moduleData['slug'] ?? 'module', CourseModule::class, 'course_id', $course->id),
                    'description' => $moduleData['description'] ?? null,
                    'sort_order' => $moduleData['sort_order'] ?? 0,
                ]);

                foreach ($moduleData['lessons'] ?? [] as $lessonData) {
                    $lesson = $module->lessons()->create([
                        'title' => $lessonData['title'] ?? 'Lesson',
                        'slug' => $this->uniqueSlug($lessonData['slug'] ?? 'lesson', Lesson::class, 'course_module_id', $module->id),
                        'description' => $lessonData['description'] ?? null,
                        'sort_order' => $lessonData['sort_order'] ?? 0,
                        'is_published' => $lessonData['is_published'] ?? false,
                        'is_optional' => $lessonData['is_optional'] ?? false,
                    ]);

                    foreach ($lessonData['blocks'] ?? [] as $blockData) {
                        $lesson->blocks()->create([
                            'type' => $blockData['type'] ?? 'TEXT',
                            'title' => $blockData['title'] ?? null,
                            'content' => $blockData['content'] ?? [],
                            'sort_order' => $blockData['sort_order'] ?? 0,
                        ]);
                    }
                }
            }

            return $course;
        });

        return response()->json([
            'message' => 'Course berhasil diimport.',
            'course_id' => $course->id,
            'course_slug' => $course->slug,
        ]);
    }

    public function importContent(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $request->validate([
            'file' => ['required', 'file', 'mimes:json', 'max:10240'],
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            abort(422, 'File JSON tidak valid.');
        }

        if (! isset($data['course'], $data['version'])) {
            abort(422, 'Format export tidak valid. File harus berisi data course dengan version.');
        }

        $modulesData = $data['course']['modules'] ?? [];

        $summary = \DB::transaction(function () use ($course, $modulesData) {
            $stats = [
                'modules_added' => 0,
                'modules_merged' => 0,
                'lessons_added' => 0,
                'lessons_merged' => 0,
                'blocks_added' => 0,
                'blocks_skipped' => 0,
            ];

            $moduleOffset = ($course->modules()->max('sort_order') ?? -1) + 1;

            foreach ($modulesData as $moduleData) {
                $moduleSlug = $moduleData['slug'] ?? 'module';
                $existingModule = $course->modules()->where('slug', $moduleSlug)->first();

                if ($existingModule) {
                    $module = $existingModule;
                    $stats['modules_merged']++;
                } else {
                    $module = $course->modules()->create([
                        'title' => $moduleData['title'] ?? 'Module',
                        'slug' => $this->uniqueSlug($moduleSlug, CourseModule::class, 'course_id', $course->id),
                        'description' => $moduleData['description'] ?? null,
                        'sort_order' => $moduleOffset++,
                    ]);
                    $stats['modules_added']++;
                }

                $lessonOffset = ($module->lessons()->max('sort_order') ?? -1) + 1;

                foreach ($moduleData['lessons'] ?? [] as $lessonData) {
                    $lessonSlug = $lessonData['slug'] ?? 'lesson';
                    $existingLesson = $module->lessons()->where('slug', $lessonSlug)->first();

                    if ($existingLesson) {
                        $lesson = $existingLesson;
                        $stats['lessons_merged']++;
                    } else {
                        $lesson = $module->lessons()->create([
                            'title' => $lessonData['title'] ?? 'Lesson',
                            'slug' => $this->uniqueSlug($lessonSlug, Lesson::class, 'course_module_id', $module->id),
                            'description' => $lessonData['description'] ?? null,
                            'sort_order' => $lessonOffset++,
                            'is_published' => $lessonData['is_published'] ?? false,
                            'is_optional' => $lessonData['is_optional'] ?? false,
                        ]);
                        $stats['lessons_added']++;
                    }

                    $blockOffset = ($lesson->blocks()->max('sort_order') ?? -1) + 1;

                    foreach ($lessonData['blocks'] ?? [] as $blockData) {
                        $blockTitle = $blockData['title'] ?? null;

                        if ($blockTitle !== null) {
                            $existingBlock = $lesson->blocks()
                                ->where('title', $blockTitle)
                                ->exists();

                            if ($existingBlock) {
                                $stats['blocks_skipped']++;

                                continue;
                            }
                        }

                        $lesson->blocks()->create([
                            'type' => $blockData['type'] ?? 'TEXT',
                            'title' => $blockTitle,
                            'content' => $blockData['content'] ?? [],
                            'sort_order' => $blockOffset++,
                        ]);
                        $stats['blocks_added']++;
                    }
                }
            }

            return $stats;
        });

        return response()->json([
            'message' => 'Content berhasil diimport.',
            ...$summary,
        ]);
    }

    private function uniqueSlug(string $slug, string $model, ?string $scopeColumn = null, ?int $scopeValue = null): string
    {
        $original = $slug;
        $counter = 1;

        while (true) {
            $query = $model::where('slug', $slug);
            if ($scopeColumn && $scopeValue) {
                $query->where($scopeColumn, $scopeValue);
            }
            if (! $query->exists()) {
                return $slug;
            }
            $slug = $original.'-'.++$counter;
        }
    }
}
