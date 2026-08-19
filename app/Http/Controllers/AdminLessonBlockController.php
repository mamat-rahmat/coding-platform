<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ReorderLessonBlocksRequest;
use App\Http\Requests\Admin\StoreLessonBlockRequest;
use App\Http\Requests\Admin\UpdateLessonBlockRequest;
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

        return Inertia::render('admin/blocks/Index', [
            'lesson' => $lesson,
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
            ->route('admin.blocks.edit', [$lesson, $block])
            ->with('success', 'Block berhasil dibuat.');
    }

    public function edit(Lesson $lesson, LessonBlock $block): Response
    {
        $this->authorize('update', $block);

        $block->load(['lesson.module.course']);

        return Inertia::render('admin/blocks/Edit', [
            'lesson' => $lesson,
            'block' => $block,
        ]);
    }

    public function update(
        UpdateLessonBlockRequest $request,
        Lesson $lesson,
        LessonBlock $block,
    ): RedirectResponse {
        $this->authorize('update', $block);

        $block->update($request->validated());

        return redirect()
            ->route('admin.blocks.index', $lesson)
            ->with('success', 'Block berhasil diperbarui.');
    }

    public function destroy(
        Lesson $lesson,
        LessonBlock $block,
    ): RedirectResponse {
        $this->authorize('delete', $block);

        $block->delete();

        return redirect()
            ->route('admin.blocks.index', $lesson)
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
}
