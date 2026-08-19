<?php

namespace Database\Seeders;

use App\LessonBlockType;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Coder Bodoh',
            'email' => 'coderbodoh@gmail.com',
            'password' => bcrypt('pass123'),
            'is_admin' => false,
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
                'text' => 'Python adalah bahasa pemrograman yang **mudah dipelajari**. Mari kita mulai dengan program pertama.\n\n## Tujuan\n\n- Memahami fungsi `print()`\n- Menjalankan kode Python pertama',
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
            'type' => LessonBlockType::HINT,
            'content' => [
                'title' => 'Petunjuk Print',
                'text' => 'Fungsi `print()` otomatis menambahkan newline di akhir output.',
            ],
            'sort_order' => 3,
        ]);

        $helloPython->blocks()->create([
            'type' => LessonBlockType::MCQ_SINGLE,
            'content' => [
                'question' => 'Apa output dari kode berikut?',
                'code' => <<<'PYTHON'
print("Hello World")
PYTHON,
                'options' => [
                    ['id' => 'a', 'text' => 'Hello'],
                    ['id' => 'b', 'text' => 'World'],
                    ['id' => 'c', 'text' => 'Hello World'],
                    ['id' => 'd', 'text' => 'Error'],
                ],
                'correct_answer' => 'c',
            ],
            'sort_order' => 4,
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

        $variables->blocks()->create([
            'type' => LessonBlockType::MCQ_MULTIPLE,
            'content' => [
                'question' => 'Mana yang termasuk tipe data Python?',
                'options' => [
                    ['id' => 'a', 'text' => 'int'],
                    ['id' => 'b', 'text' => 'string'],
                    ['id' => 'c', 'text' => 'loop'],
                    ['id' => 'd', 'text' => 'float'],
                ],
                'correct_answers' => ['a', 'b', 'd'],
            ],
            'sort_order' => 3,
        ]);

        $variables->blocks()->create([
            'type' => LessonBlockType::CODE_FILL,
            'content' => [
                'code_template' => 'name = {{A}}\nprint({{A}})',
                'blanks' => [
                    ['id' => 'A', 'answer' => '"Budi"'],
                ],
            ],
            'sort_order' => 4,
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
                'text' => 'Statement `if` memungkinkan program menjalankan kode hanya ketika kondisi tertentu terpenuhi.',
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

        $ifStatements->blocks()->create([
            'type' => LessonBlockType::CODE_REORDER,
            'content' => [
                'lines' => [
                    'age = 13',
                    'if age >= 13:',
                    '    print("Teenager")',
                ],
                'correct_order' => [0, 1, 2],
            ],
            'sort_order' => 3,
        ]);

        $functions = $controlFlow->lessons()->create([
            'title' => 'Functions',
            'slug' => 'functions',
            'description' => 'Belajar membuat fungsi sendiri.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $functions->blocks()->create([
            'type' => LessonBlockType::TEXT,
            'content' => [
                'text' => 'Fungsi adalah blok kode reusable. Didefinisikan dengan `def`.',
            ],
            'sort_order' => 1,
        ]);

        $functions->blocks()->create([
            'type' => LessonBlockType::CODE_CHALLENGE,
            'content' => [
                'prompt' => 'Buat fungsi `greet(name)` yang mencetak "Hello, <name>!" lalu panggil dengan nama "Budi".',
                'starter_code' => <<<'PYTHON'
def greet(name):
    # lengkapi fungsi ini
    pass

# panggil greet("Budi")
PYTHON,
                'testcases' => [
                    [
                        'id' => 'tc1',
                        'input' => '',
                        'expected_output' => 'Hello, Budi!',
                        'hidden' => false,
                    ],
                    [
                        'id' => 'tc2',
                        'input' => '',
                        'expected_output' => 'Hello, Andi!',
                        'hidden' => true,
                    ],
                ],
            ],
            'sort_order' => 2,
        ]);
    }
}
