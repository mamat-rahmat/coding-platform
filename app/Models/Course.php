<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'language',
        'level',
        'thumbnail',
        'xp_reward',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CourseModule, Course>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class);
    }

    /**
     * @return HasManyThrough<Lesson, CourseModule, Course>
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(
            Lesson::class,
            CourseModule::class,
            'course_id',
            'course_module_id',
        );
    }

    /**
     * @return BelongsToMany<Course, User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
