<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\MoveLessonBlockRequest;
use App\Http\Requests\Admin\ReorderLessonBlocksRequest;
use App\Http\Requests\Admin\StoreLessonBlockRequest;
use App\Http\Requests\Admin\UpdateLessonBlockRequest;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminLessonBlockController extends Controller
{
    public function index(Lesson $lesson): Response
    {
        $this->authorize('viewAny', LessonBlock::class);
        $this->authorize('view', $lesson);

        $lesson->load([
            'module.course',
            'blocks' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $courseLessons = CourseModule::where('course_id', $lesson->module->course_id)
            ->with(['lessons' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (CourseModule $module) => [
                'module_id' => $module->id,
                'module_title' => $module->title,
                'lessons' => $module->lessons->map(fn (Lesson $l) => [
                    'id' => $l->id,
                    'title' => $l->title,
                    'is_optional' => $l->is_optional,
                ]),
            ]);

        return Inertia::render('admin/blocks/Index', [
            'lesson' => $lesson,
            'courseLessons' => $courseLessons,
        ]);
    }

    public function create(Lesson $lesson): Response
    {
        $this->authorize('create', LessonBlock::class);
        $this->authorize('view', $lesson);

        $nextSortOrder = ($lesson->blocks()->max('sort_order') ?? 0) + 1;

        return Inertia::render('admin/blocks/Create', [
            'lesson' => $lesson,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    public function store(
        StoreLessonBlockRequest $request,
        Lesson $lesson,
    ): RedirectResponse {
        $this->authorize('create', LessonBlock::class);

        $block = $lesson->blocks()->create($request->validated());

        return redirect()
            ->route('admin.blocks.edit', $block)
            ->with('success', 'Block berhasil dibuat.');
    }

    public function edit(LessonBlock $block): Response
    {
        $this->authorize('update', $block);

        $block->load(['lesson.module.course']);

        return Inertia::render('admin/blocks/Edit', [
            'lesson' => $block->lesson,
            'block' => $block,
        ]);
    }

    public function update(
        UpdateLessonBlockRequest $request,
        LessonBlock $block,
    ): RedirectResponse {
        $this->authorize('update', $block);

        $block->update($request->validated());

        return redirect()
            ->route('admin.blocks.index', $block->lesson)
            ->with('success', 'Block berhasil diperbarui.');
    }

    public function destroy(LessonBlock $block): RedirectResponse
    {
        $this->authorize('delete', $block);

        $lessonId = $block->lesson_id;

        $block->delete();

        return redirect()
            ->route('admin.blocks.index', $lessonId)
            ->with('success', 'Block berhasil dihapus.');
    }

    public function reorder(
        ReorderLessonBlocksRequest $request,
        Lesson $lesson,
    ): RedirectResponse {
        $this->authorize('update', $lesson);

        DB::transaction(function () use ($request, $lesson) {
            foreach ($request->validated()['blocks'] as $item) {
                LessonBlock::where('id', $item['id'])
                    ->where('lesson_id', $lesson->id)
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        return back()->with('success', 'Urutan block berhasil diperbarui.');
    }

    public function move(
        MoveLessonBlockRequest $request,
        LessonBlock $block,
    ): RedirectResponse {
        $this->authorize('update', $block);

        $targetLessonId = $request->validated()['target_lesson_id'];
        $sourceLessonId = $block->lesson_id;

        if ($targetLessonId === $sourceLessonId) {
            return back()->withErrors(['target_lesson_id' => 'Block sudah berada di lesson ini.']);
        }

        DB::transaction(function () use ($block, $targetLessonId, $sourceLessonId) {
            $newSortOrder = (LessonBlock::where('lesson_id', $targetLessonId)->max('sort_order') ?? 0) + 1;

            $block->update([
                'lesson_id' => $targetLessonId,
                'sort_order' => $newSortOrder,
            ]);

            $remaining = LessonBlock::where('lesson_id', $sourceLessonId)
                ->orderBy('sort_order')
                ->get();

            foreach ($remaining as $index => $item) {
                $item->update(['sort_order' => $index + 1]);
            }
        });

        return redirect()
            ->route('admin.blocks.index', $targetLessonId)
            ->with('success', 'Block berhasil dipindahkan.');
    }
}
