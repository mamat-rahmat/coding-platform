<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property bool $is_completed
 * @property bool $is_locked
 */
class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_module_id',
        'title',
        'slug',
        'description',
        'sort_order',
        'is_published',
        'is_optional',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_optional' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<CourseModule, Lesson>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    /**
     * @return HasMany<LessonBlock, Lesson>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(LessonBlock::class);
    }

    /**
     * @return HasMany<LessonProgress, Lesson>
     */
    public function progresses(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function orderedInCourse(): Collection
    {
        $course = $this->module->course;

        return $course->lessons()
            ->where('is_published', true)
            ->with('module:id,course_id,sort_order')
            ->get([
                'lessons.id',
                'lessons.course_module_id',
                'lessons.title',
                'lessons.slug',
                'lessons.description',
                'lessons.sort_order',
                'lessons.is_optional',
            ])
            ->sortBy(fn (Lesson $lesson) => [
                $lesson->module->sort_order,
                $lesson->sort_order,
                $lesson->id,
            ])
            ->values();
    }

    public function isUnlockedFor(User $user): bool
    {
        if (! $this->module->isUnlockedFor($user)) {
            return false;
        }

        $siblings = Lesson::query()
            ->where('course_module_id', $this->course_module_id)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $position = $siblings->search(fn (Lesson $lesson) => $lesson->id === $this->id);

        if ($position === false || $position === 0) {
            return true;
        }

        for ($i = $position - 1; $i >= 0; $i--) {
            if (! $siblings[$i]->is_optional) {
                return $user->lessonProgresses()
                    ->where('lesson_id', $siblings[$i]->id)
                    ->whereNotNull('completed_at')
                    ->exists();
            }
        }

        return true;
    }
}
