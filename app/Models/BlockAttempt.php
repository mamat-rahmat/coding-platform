<?php

namespace App\Models;

use Database\Factories\BlockAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockAttempt extends Model
{
    /** @use HasFactory<BlockAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_block_id',
        'selected_answer',
        'is_correct',
        'attempt_data',
        'score',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'attempt_data' => 'array',
            'answered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<LessonBlock, $this>
     */
    public function lessonBlock(): BelongsTo
    {
        return $this->belongsTo(LessonBlock::class);
    }
}
