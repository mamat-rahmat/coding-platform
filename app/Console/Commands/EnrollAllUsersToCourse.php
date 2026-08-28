<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use Illuminate\Console\Command;

class EnrollAllUsersToCourse extends Command
{
    protected $signature = 'app:enroll-all-users-to-course {course : Slug atau ID course}';

    protected $description = 'Mendaftarkan semua user ke sebuah course (jika belum terdaftar)';

    public function handle(): int
    {
        $course = Course::where('slug', $this->argument('course'))
            ->orWhere('id', $this->argument('course'))
            ->firstOrFail();

        $userIds = User::pluck('id');

        $result = $course->users()->syncWithoutDetaching($userIds);

        $attached = count($result['attached']);
        $already = $userIds->count() - $attached;

        $this->info("Course: {$course->title} ({$course->slug})");
        $this->info("User baru didaftarkan: {$attached}");
        $this->info("User sudah terdaftar: {$already}");

        return self::SUCCESS;
    }
}
