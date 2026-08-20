<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreModuleRequest;
use App\Http\Requests\Admin\UpdateModuleRequest;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminModuleController extends Controller
{
    public function index(Course $course): Response
    {
        $this->authorize('viewAny', CourseModule::class);
        $this->authorize('view', $course);

        $course->load([
            'modules' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $course->loadCount(['modules', 'lessons']);

        return Inertia::render('admin/modules/Index', [
            'course' => $course,
        ]);
    }

    public function create(Course $course): Response
    {
        $this->authorize('create', CourseModule::class);
        $this->authorize('view', $course);

        $nextSortOrder = ($course->modules()->max('sort_order') ?? 0) + 1;

        return Inertia::render('admin/modules/Create', [
            'course' => $course,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    public function store(
        StoreModuleRequest $request,
        Course $course,
    ): RedirectResponse {
        $this->authorize('create', CourseModule::class);

        $module = $course->modules()->create($request->validated());

        return redirect()
            ->route('admin.modules.show', $module)
            ->with('success', 'Module berhasil dibuat.');
    }

    public function show(CourseModule $module): Response
    {
        $this->authorize('view', $module);

        $module->load([
            'course',
            'lessons' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return Inertia::render('admin/modules/Show', [
            'course' => $module->course,
            'module' => $module,
        ]);
    }

    public function edit(CourseModule $module): Response
    {
        $this->authorize('update', $module);

        $module->load('course');

        return Inertia::render('admin/modules/Edit', [
            'course' => $module->course,
            'module' => $module,
        ]);
    }

    public function update(
        UpdateModuleRequest $request,
        CourseModule $module,
    ): RedirectResponse {
        $this->authorize('update', $module);

        $module->update($request->validated());

        return redirect()
            ->route('admin.modules.show', $module)
            ->with('success', 'Module berhasil diperbarui.');
    }

    public function destroy(CourseModule $module): RedirectResponse
    {
        $this->authorize('delete', $module);

        $courseId = $module->course_id;

        $module->delete();

        return redirect()
            ->route('admin.modules.index', $courseId)
            ->with('success', 'Module berhasil dihapus.');
    }
}
