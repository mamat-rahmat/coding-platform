<?php

namespace Database\Seeders;

use App\LessonBlockType;
use App\Models\Course;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = \App\Models\User::create([
            'name' => 'Coder Bodoh',
            'email' => 'coderbodoh@gmail.com',
            'password' => bcrypt('pass123'),
        ]);

        $course = Course::create([
            'title' => 'Python Fundamentals',
            'slug' => 'python-fundamentals',
            'description' => 'Belajar dasar pemrograman Python dari awal.',
            'language' => 'python',
            'level' => 'beginner',
            'xp_reward' => 500,
            'is_published' => true,
        ]);

        $basics = $course->modules()->create([
            'title' => 'Python Basics',
            'slug' => 'python-basics',
            'description' => 'Mengenal dasar-dasar Python.',
            'sort_order' => 1,
        ]);

        $helloPython = $basics->lessons()->create([
            'title' => 'Hello Python',
            'slug' => 'hello-python',
            'description' => 'Program Python pertama kamu.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $helloPython->blocks()->create([
            'type' => LessonBlockType::TEXT,
            'content' => [
                'text' => 'Python adalah bahasa pemrograman yang mudah dipelajari. Mari kita mulai dengan program pertama.',
            ],
            'sort_order' => 1,
        ]);

        $helloPython->blocks()->create([
            'type' => LessonBlockType::CODE_EXAMPLE,
            'content' => [
                'language' => 'python',
                'code' => 'print("Hello, World!")',
            ],
            'sort_order' => 2,
        ]);

        $helloPython->blocks()->create([
            'type' => LessonBlockType::MCQ_SINGLE,
            'content' => [
                'question' => 'Apa output dari kode berikut?',
                'code' => <<<'PYTHON'
        print("Hello World")
        PYTHON,
                'options' => [
                    [
                        'id' => 'a',
                        'text' => 'Hello',
                    ],
                    [
                        'id' => 'b',
                        'text' => 'World',
                    ],
                    [
                        'id' => 'c',
                        'text' => 'Hello World',
                    ],
                    [
                        'id' => 'd',
                        'text' => 'Error',
                    ],
                ],
                'correct_answer' => 'c',
            ],
            'sort_order' => 3,
        ]);

        $variables = $basics->lessons()->create([
            'title' => 'Variables',
            'slug' => 'variables',
            'description' => 'Belajar menyimpan data menggunakan variabel.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $variables->blocks()->create([
            'type' => LessonBlockType::TEXT,
            'content' => [
                'text' => 'Variabel digunakan untuk menyimpan nilai yang dapat digunakan kembali dalam program.',
            ],
            'sort_order' => 1,
        ]);

        $variables->blocks()->create([
            'type' => LessonBlockType::CODE_EXAMPLE,
            'content' => [
                'language' => 'python',
                'code' => <<<'PYTHON'
name = "Budi"
age = 13

print(name)
print(age)
PYTHON,
            ],
            'sort_order' => 2,
        ]);

        $controlFlow = $course->modules()->create([
            'title' => 'Control Flow',
            'slug' => 'control-flow',
            'description' => 'Belajar membuat program mengambil keputusan.',
            'sort_order' => 2,
        ]);

        $ifStatements = $controlFlow->lessons()->create([
            'title' => 'If Statements',
            'slug' => 'if-statements',
            'description' => 'Belajar membuat keputusan menggunakan if.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $ifStatements->blocks()->create([
            'type' => LessonBlockType::TEXT,
            'content' => [
                'text' => 'Statement if memungkinkan program menjalankan kode hanya ketika kondisi tertentu terpenuhi.',
            ],
            'sort_order' => 1,
        ]);

        $ifStatements->blocks()->create([
            'type' => LessonBlockType::CODE_EXAMPLE,
            'content' => [
                'language' => 'python',
                'code' => <<<'PYTHON'
age = 13

if age >= 13:
    print("Teenager")
PYTHON,
            ],
            'sort_order' => 2,
        ]);
    }
}