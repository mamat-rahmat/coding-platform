<?php

namespace App\Policies;

use App\Models\LessonBlock;
use App\Models\User;

class LessonBlockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, LessonBlock $block): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, LessonBlock $block): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, LessonBlock $block): bool
    {
        return $user->is_admin;
    }
}
