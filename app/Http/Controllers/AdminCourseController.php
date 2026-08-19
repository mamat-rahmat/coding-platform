<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminCourseController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::query()
            ->withCount('modules')
            ->orderBy('title')
            ->get();

        return Inertia::render('admin/courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Course::class);

        return Inertia::render('admin/courses/Create');
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $course = Course::create($request->validated());

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('success', 'Course berhasil dibuat.');
    }

    public function show(Course $course): Response
    {
        $this->authorize('view', $course);

        $course->load([
            'modules' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $course->loadCount(['modules', 'lessons']);

        return Inertia::render('admin/courses/Show', [
            'course' => $course,
        ]);
    }

    public function edit(Course $course): Response
    {
        $this->authorize('update', $course);

        return Inertia::render('admin/courses/Edit', [
            'course' => $course,
        ]);
    }

    public function update(
        UpdateCourseRequest $request,
        Course $course,
    ): RedirectResponse {
        $this->authorize('update', $course);

        $course->update($request->validated());

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('success', 'Course berhasil diperbarui.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Course berhasil dihapus.');
    }
}
