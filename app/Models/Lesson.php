<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(LessonBlock::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

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
        $ordered = $this->orderedInCourse();

        $position = $ordered->search(fn (Lesson $lesson) => $lesson->id === $this->id);

        if ($position === false || $position === 0) {
            return true;
        }

        $previous = $ordered[$position - 1];

        return $user->lessonProgresses()
            ->where('lesson_id', $previous->id)
            ->whereNotNull('completed_at')
            ->exists();
    }
}
