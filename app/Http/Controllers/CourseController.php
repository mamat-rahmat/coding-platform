<?php

namespace App\Http\Controllers;

use App\Models\Course;
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

    public function show(Course $course): Response
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

        return Inertia::render('Courses/Show', [
            'course' => $course,
        ]);
    }
}
