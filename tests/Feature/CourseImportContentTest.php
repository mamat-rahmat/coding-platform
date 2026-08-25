<?php

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonBlock;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function makeExportJson(array $modules): array
{
    return [
        'version' => '1.0',
        'exported_at' => now()->toIso8601String(),
        'course' => [
            'title' => 'Export Source',
            'slug' => 'export-source',
            'description' => null,
            'language' => 'python',
            'level' => 'beginner',
            'xp_reward' => 500,
            'is_published' => true,
            'modules' => $modules,
        ],
    ];
}

function uploadImportContent(Course $course, array $data)
{
    $tmpPath = tempnam(sys_get_temp_dir(), 'import_').'.json';
    file_put_contents($tmpPath, json_encode($data));

    $file = new UploadedFile($tmpPath, 'content.json', 'application/json', null, true);

    return test()->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.courses.importContent', $course), ['file' => $file]);
}

test('guests cannot import content', function () {
    $course = Course::factory()->create();

    $this->post(route('admin.courses.importContent', $course))
        ->assertRedirect(route('login'));
});

test('non-admin users cannot import content', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $course = Course::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.courses.importContent', $course))
        ->assertForbidden();
});

test('import content into empty course creates all modules', function () {
    $course = Course::factory()->create();

    $data = makeExportJson([
        [
            'title' => 'Variables',
            'slug' => 'variables',
            'description' => 'About variables',
            'sort_order' => 0,
            'lessons' => [
                [
                    'title' => 'Intro',
                    'slug' => 'intro',
                    'description' => null,
                    'sort_order' => 0,
                    'is_published' => true,
                    'is_optional' => false,
                    'blocks' => [
                        ['type' => 'TEXT', 'title' => 'Welcome', 'content' => ['text' => 'Hello'], 'sort_order' => 0],
                    ],
                ],
            ],
        ],
    ]);

    $response = uploadImportContent($course, $data);

    $response->assertOk();
    $response->assertJson(['modules_added' => 1, 'modules_merged' => 0, 'lessons_added' => 1, 'lessons_merged' => 0, 'blocks_added' => 1, 'blocks_updated' => 0]);

    expect(CourseModule::where('course_id', $course->id)->count())->toBe(1)
        ->and(Lesson::where('course_module_id', $course->modules()->first()->id)->count())->toBe(1)
        ->and(LessonBlock::where('lesson_id', $course->modules()->first()->lessons()->first()->id)->count())->toBe(1);
});

test('import content merges lessons into existing module', function () {
    $course = Course::factory()->create();
    $existingModule = CourseModule::factory()->create([
        'course_id' => $course->id,
        'slug' => 'variables',
        'sort_order' => 0,
    ]);
    $existingLesson = Lesson::factory()->create([
        'course_module_id' => $existingModule->id,
        'slug' => 'intro',
        'sort_order' => 0,
    ]);
    LessonBlock::factory()->create([
        'lesson_id' => $existingLesson->id,
        'title' => 'Welcome',
        'sort_order' => 0,
    ]);

    $data = makeExportJson([
        [
            'title' => 'Variables',
            'slug' => 'variables',
            'description' => 'About variables',
            'sort_order' => 0,
            'lessons' => [
                [
                    'title' => 'Intro',
                    'slug' => 'intro',
                    'description' => null,
                    'sort_order' => 0,
                    'is_published' => true,
                    'is_optional' => false,
                    'blocks' => [
                        ['type' => 'TEXT', 'title' => 'Welcome', 'content' => ['text' => 'Hello'], 'sort_order' => 0],
                        ['type' => 'TEXT', 'title' => 'Goodbye', 'content' => ['text' => 'Bye'], 'sort_order' => 1],
                    ],
                ],
                [
                    'title' => 'Advanced',
                    'slug' => 'advanced',
                    'description' => null,
                    'sort_order' => 1,
                    'is_published' => true,
                    'is_optional' => false,
                    'blocks' => [],
                ],
            ],
        ],
    ]);

    $response = uploadImportContent($course, $data);

    $response->assertOk();
    $response->assertJson(['modules_added' => 0, 'modules_merged' => 1, 'lessons_added' => 1, 'lessons_merged' => 1, 'blocks_added' => 1, 'blocks_updated' => 1]);

    expect($existingModule->fresh()->lessons()->count())->toBe(2)
        ->and($existingLesson->fresh()->blocks()->count())->toBe(2);
});

test('import content deduplicates module slug when creating new module', function () {
    $course = Course::factory()->create();
    CourseModule::factory()->create([
        'course_id' => $course->id,
        'slug' => 'variables',
        'sort_order' => 0,
    ]);

    $data = makeExportJson([
        [
            'title' => 'Variables',
            'slug' => 'variables',
            'description' => null,
            'sort_order' => 0,
            'lessons' => [],
        ],
        [
            'title' => 'Loops',
            'slug' => 'loops',
            'description' => null,
            'sort_order' => 1,
            'lessons' => [],
        ],
    ]);

    $response = uploadImportContent($course, $data);

    $response->assertOk();
    $response->assertJson(['modules_added' => 1, 'modules_merged' => 1]);

    expect(CourseModule::where('course_id', $course->id)->orderBy('sort_order')->pluck('slug')->toArray())->toBe(['variables', 'loops']);
});

test('import content with null title block always adds', function () {
    $course = Course::factory()->create();
    $module = CourseModule::factory()->create([
        'course_id' => $course->id,
        'slug' => 'mod1',
        'sort_order' => 0,
    ]);
    $lesson = Lesson::factory()->create([
        'course_module_id' => $module->id,
        'slug' => 'les1',
        'sort_order' => 0,
    ]);
    LessonBlock::factory()->create([
        'lesson_id' => $lesson->id,
        'title' => null,
        'sort_order' => 0,
    ]);

    $data = makeExportJson([
        [
            'title' => 'Mod1',
            'slug' => 'mod1',
            'description' => null,
            'sort_order' => 0,
            'lessons' => [
                [
                    'title' => 'Les1',
                    'slug' => 'les1',
                    'description' => null,
                    'sort_order' => 0,
                    'is_published' => true,
                    'is_optional' => false,
                    'blocks' => [
                        ['type' => 'TEXT', 'title' => null, 'content' => ['text' => 'New'], 'sort_order' => 0],
                    ],
                ],
            ],
        ],
    ]);

    $response = uploadImportContent($course, $data);

    $response->assertOk();
    $response->assertJson(['blocks_added' => 1, 'blocks_updated' => 0]);

    expect($lesson->fresh()->blocks()->count())->toBe(2);
});

test('import content appends modules after existing sort order', function () {
    $course = Course::factory()->create();
    CourseModule::factory()->create([
        'course_id' => $course->id,
        'slug' => 'existing',
        'sort_order' => 5,
    ]);

    $data = makeExportJson([
        [
            'title' => 'New Module',
            'slug' => 'new-module',
            'description' => null,
            'sort_order' => 0,
            'lessons' => [],
        ],
    ]);

    $response = uploadImportContent($course, $data);

    $response->assertOk();

    $newModule = CourseModule::where('course_id', $course->id)->where('slug', 'new-module')->first();
    expect($newModule->sort_order)->toBe(6);
});
