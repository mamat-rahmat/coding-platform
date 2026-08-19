<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\CourseModule;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminLessonController extends Controller
{
    public function index(CourseModule $module): Response
    {
        $this->authorize('viewAny', Lesson::class);
        $this->authorize('view', $module);

        $module->load([
            'course',
            'lessons' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return Inertia::render('admin/lessons/Index', [
            'module' => $module,
        ]);
    }

    public function create(CourseModule $module): Response
    {
        $this->authorize('create', Lesson::class);
        $this->authorize('view', $module);

        $nextSortOrder = ($module->lessons()->max('sort_order') ?? 0) + 1;

        return Inertia::render('admin/lessons/Create', [
            'module' => $module,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    public function store(
        StoreLessonRequest $request,
        CourseModule $module,
    ): RedirectResponse {
        $this->authorize('create', Lesson::class);

        $lesson = $module->lessons()->create($request->validated());

        return redirect()
            ->route('admin.lessons.show', $lesson)
            ->with('success', 'Lesson berhasil dibuat.');
    }

    public function show(Lesson $lesson): Response
    {
        $this->authorize('view', $lesson);

        $lesson->load([
            'module.course',
            'blocks' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return Inertia::render('admin/lessons/Show', [
            'lesson' => $lesson,
        ]);
    }

    public function edit(Lesson $lesson): Response
    {
        $this->authorize('update', $lesson);

        $lesson->load(['module.course']);

        return Inertia::render('admin/lessons/Edit', [
            'lesson' => $lesson,
        ]);
    }

    public function update(
        UpdateLessonRequest $request,
        Lesson $lesson,
    ): RedirectResponse {
        $this->authorize('update', $lesson);

        $lesson->update($request->validated());

        return redirect()
            ->route('admin.lessons.show', $lesson)
            ->with('success', 'Lesson berhasil diperbarui.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return redirect()
            ->route('admin.lessons.index', $lesson->course_module_id)
            ->with('success', 'Lesson berhasil dihapus.');
    }
}
