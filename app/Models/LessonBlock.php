<?php

namespace App\Models;

use App\LessonBlockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'type',
        'content',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => LessonBlockType::class,
            'content' => 'array',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function blockAttempts(): HasMany
    {
        return $this->hasMany(BlockAttempt::class);
    }
}
