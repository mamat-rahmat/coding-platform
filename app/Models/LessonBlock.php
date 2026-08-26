<?php

namespace App\Models;

use App\LessonBlockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property bool $is_answered
 * @property bool|null $is_correct
 * @property mixed|null $selected_answer
 * @property mixed|null $attempt_data
 */
class LessonBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'type',
        'title',
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

    /**
     * @return BelongsTo<Lesson, LessonBlock>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * @return HasMany<BlockAttempt, LessonBlock>
     */
    public function blockAttempts(): HasMany
    {
        return $this->hasMany(BlockAttempt::class);
    }
}
