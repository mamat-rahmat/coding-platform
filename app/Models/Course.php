<?php

namespace App\Models;

use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class);
    }

}
