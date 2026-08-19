<?php

namespace App\Policies;

use App\Models\CourseModule;
use App\Models\User;

class CourseModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, CourseModule $module): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, CourseModule $module): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, CourseModule $module): bool
    {
        return $user->is_admin;
    }
}
