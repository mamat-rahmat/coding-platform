<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

function leaderboardCourse(int $lessonCount): array
{
    $course = Course::factory()->create(['xp_reward' => 100]);
    $module = CourseModule::factory()->create(['course_id' => $course->id]);

    $lessons = collect();
    for ($i = 0; $i < $lessonCount; $i++) {
        $lessons->push(
            Lesson::factory()->create([
                'course_module_id' => $module->id,
                'is_published' => true,
                'sort_order' => $i + 1,
            ]),
        );
    }

    return [$course, $lessons];
}

function completeLessons(User $user, Collection $lessons, int $count): void
{
    $lessons->take($count)->each(function (Lesson $lesson) use ($user) {
        LessonProgress::factory()->create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'completed_at' => now(),
        ]);
    });
}

function enrollUser(Course $course, User $user): void
{
    $course->users()->attach($user->id);
}

test('guests can view the courses index', function () {
    Course::factory()->create(['is_published' => true]);
    Course::factory()->create(['is_published' => false]);

    $this->get(route('courses.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Index')
            ->has('courses', 1));
});

test('guests can view the leaderboard', function () {
    [$course, $lessons] = leaderboardCourse(2);

    $this->get(route('courses.leaderboard', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Leaderboard')
            ->where('currentUserRank', null));
});

test('leaderboard shows 404 for unpublished courses', function () {
    $course = Course::factory()->create(['is_published' => false]);

    $this->actingAs(User::factory()->create())
        ->get(route('courses.leaderboard', $course->slug))
        ->assertNotFound();
});

test('leaderboard lists participants sorted by completed lessons', function () {
    [$course, $lessons] = leaderboardCourse(3);

    $ahead = User::factory()->create(['name' => 'Ahead User']);
    $behind = User::factory()->create(['name' => 'Behind User']);
    $current = User::factory()->create(['name' => 'Current User']);

    enrollUser($course, $ahead);
    enrollUser($course, $current);
    enrollUser($course, $behind);

    completeLessons($ahead, $lessons, 3);
    completeLessons($current, $lessons, 2);
    completeLessons($behind, $lessons, 1);

    $this->actingAs($current)
        ->get(route('courses.leaderboard', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Leaderboard')
            ->where('course.slug', $course->slug)
            ->where('currentUserRank', 2)
            ->where('leaderboard.0.name', 'Ahead User')
            ->where('leaderboard.0.rank', 1)
            ->where('leaderboard.0.percentage', 100)
            ->where('leaderboard.0.xp', 100)
            ->where('leaderboard.1.name', 'Current User')
            ->where('leaderboard.1.rank', 2)
            ->where('leaderboard.1.is_current_user', true)
            ->where('leaderboard.1.percentage', 67)
            ->where('leaderboard.1.xp', 67)
            ->where('leaderboard.2.name', 'Behind User')
            ->where('leaderboard.2.rank', 3)
            ->where('leaderboard.2.is_current_user', false));
});

test('ties are broken by earliest completion', function () {
    [$course, $lessons] = leaderboardCourse(2);

    $early = User::factory()->create(['name' => 'Early User']);
    $late = User::factory()->create(['name' => 'Late User']);

    enrollUser($course, $early);
    enrollUser($course, $late);

    LessonProgress::factory()->create([
        'user_id' => $early->id,
        'lesson_id' => $lessons[0]->id,
        'completed_at' => now()->subDays(2),
    ]);

    LessonProgress::factory()->create([
        'user_id' => $late->id,
        'lesson_id' => $lessons[0]->id,
        'completed_at' => now()->subDay(),
    ]);

    $this->actingAs($early)
        ->get(route('courses.leaderboard', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('leaderboard.0.name', 'Early User')
            ->where('leaderboard.1.name', 'Late User')
            ->where('leaderboard.1.is_current_user', false));
});

test('leaderboard is empty and currentUserRank is null when user has no progress', function () {
    [$course, $lessons] = leaderboardCourse(2);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('courses.leaderboard', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentUserRank', null)
            ->where('leaderboard', []));
});

test('enrolled user with 0 completed lessons appears on leaderboard', function () {
    [$course, $lessons] = leaderboardCourse(3);

    $user = User::factory()->create(['name' => 'New User']);
    enrollUser($course, $user);

    $this->actingAs($user)
        ->get(route('courses.leaderboard', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentUserRank', 1)
            ->where('leaderboard.0.name', 'New User')
            ->where('leaderboard.0.completed_lessons', 0)
            ->where('leaderboard.0.percentage', 0)
            ->where('leaderboard.0.xp', 0));
});

test('leaderboard excludes admin users and keeps ranks contiguous', function () {
    [$course, $lessons] = leaderboardCourse(3);

    $regular = User::factory()->create(['name' => 'Regular User']);
    $admin = User::factory()->admin()->create(['name' => 'Admin User']);

    enrollUser($course, $regular);
    enrollUser($course, $admin);

    completeLessons($regular, $lessons, 3);
    completeLessons($admin, $lessons, 3);

    $this->actingAs($regular)
        ->get(route('courses.leaderboard', $course->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('leaderboard.0.name', 'Regular User')
            ->where('leaderboard.0.rank', 1)
            ->where('leaderboard.0.is_current_user', true)
            ->where('leaderboard', fn ($list) => $list->toArray() === [[
                'rank' => 1,
                'user_id' => $regular->id,
                'name' => 'Regular User',
                'completed_lessons' => 3,
                'total_lessons' => 3,
                'percentage' => 100,
                'xp' => 100,
                'is_current_user' => true,
            ]]));
});
