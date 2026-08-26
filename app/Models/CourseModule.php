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
class CourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'slug',
        'description',
        'sort_order',
    ];

    /**
     * @return BelongsTo<Course, CourseModule>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return HasMany<Lesson, CourseModule>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function lessonsOrdered(): Collection
    {
        return $this->lessons()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function isCompletedBy(User $user): bool
    {
        $lessons = $this->lessonsOrdered()->where('is_optional', false);

        if ($lessons->isEmpty()) {
            return true;
        }

        return $user->lessonProgresses()
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->count() === $lessons->count();
    }

    public function isUnlockedFor(User $user): bool
    {
        $modules = $this->course->modules()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $position = $modules->search(
            fn (CourseModule $module) => $module->id === $this->id,
        );

        if ($position === false || $position === 0) {
            return true;
        }

        for ($i = 0; $i < $position; $i++) {
            if (! $modules[$i]->isCompletedBy($user)) {
                return false;
            }
        }

        return true;
    }
}
