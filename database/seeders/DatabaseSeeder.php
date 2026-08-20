<?php

namespace Database\Seeders;

use App\LessonBlockType;
use App\Models\Course;
use App\Models\Lesson;
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
            'description' => 'Belajar dasar pemrograman Python dari nol. Materi mencakup variabel, operator, control flow, fungsi, struktur data, dan string manipulation.',
            'language' => 'python',
            'level' => 'beginner',
            'xp_reward' => 1000,
            'is_published' => true,
        ]);

        $this->seedModule1($course);
        $this->seedModule2($course);
        $this->seedModule3($course);
        $this->seedModule4($course);
        $this->seedModule5($course);
        $this->seedModule6($course);
    }

    private function seedModule1(Course $course): void
    {
        $module = $course->modules()->create([
            'title' => 'Pengenalan Python',
            'slug' => 'pengenalan-python',
            'description' => 'Mengenal Python, variabel, tipe data, dan input/output.',
            'sort_order' => 1,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Hello Python',
            'slug' => 'hello-python',
            'description' => 'Program Python pertama kamu.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
Python adalah bahasa pemrograman yang **mudah dipelajari** dan populer. Mari mulai dengan program pertama.

## Tujuan

- Memahami fungsi `print()`
- Menjalankan kode Python pertama
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
print("Hello, World!")
PYTHON,
            language: 'python',
            markdown: <<<'MD'
Fungsi `print()` akan menampilkan teks ke layar. Perhatikan bahwa teks yang
dicetak diapit tanda **kutip dua** (`"`).
MD);

        $this->hint($lesson, 3, 'Petunjuk Print', <<<'MD'
Fungsi `print()` otomatis menambahkan **newline** di akhir output. Jika ingin tanpa newline, gunakan parameter `end`:

```python
print("Hello", end=" ")
print("World")
```
MD);

        $this->mcqSingle($lesson, 4, 'Apa output dari kode berikut?', <<<'PYTHON'
print("Hello World")
PYTHON, [
            ['id' => 'a', 'text' => 'Hello'],
            ['id' => 'b', 'text' => 'World'],
            ['id' => 'c', 'text' => 'Hello World'],
            ['id' => 'd', 'text' => 'Error'],
        ], 'c');

        $lesson = $module->lessons()->create([
            'title' => 'Variabel & Tipe Data',
            'slug' => 'variabel-dan-tipe-data',
            'description' => 'Belajar menyimpan data dengan variabel.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
Variabel digunakan untuk **menyimpan nilai** yang dapat digunakan kembali. Python tidak perlu deklarasi tipe — tipe ditentukan otomatis.

## Tipe Data Dasar

| Tipe | Contoh | Deskripsi |
|------|--------|-----------|
| `int` | `13` | Bilangan bulat |
| `float` | `3.14` | Bilangan desimal |
| `str` | `"Budi"` | Teks / string |
| `bool` | `True` | Benar atau salah |
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
name = "Budi"
age = 13
height = 165.5
is_student = True

print(name)
print(age)
print(height)
print(is_student)
PYTHON);

        $this->mcqMultiple($lesson, 3, 'Mana yang termasuk tipe data Python?', null, [
            ['id' => 'a', 'text' => 'int'],
            ['id' => 'b', 'text' => 'string'],
            ['id' => 'c', 'text' => 'loop'],
            ['id' => 'd', 'text' => 'float'],
        ], ['a', 'b', 'd']);

        $this->codeFill($lesson, 4, <<<'PYTHON'
name = {{A}}
age = {{B}}

print(name)
print(age)
PYTHON, [
            ['id' => 'A', 'answer' => '"Andi"'],
            ['id' => 'B', 'answer' => '15'],
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Input & Output',
            'slug' => 'input-output',
            'description' => 'Berinteraksi dengan user via input().',
            'sort_order' => 3,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
Fungsi `input()` membaca input dari user. Hasilnya selalu **string**, jadi untuk angka perlu konversi dengan `int()` atau `float()`.
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
name = input("Nama kamu siapa? ")
print("Halo, " + name + "!")

age = int(input("Umur berapa? "))
print("Tahun depan umur kamu", age + 1)
PYTHON);

        $this->codeChallenge($lesson, 3, <<<'MD'
Buat program yang meminta user memasukkan **nama** dan **umur**, lalu cetak:

```
Halo, [nama]! Kamu berumur [umur] tahun.
```
MD, <<<'PYTHON'
# Tulis kode di sini
name = input()
age = input()
PYTHON, [
            [
                'id' => 'tc1',
                'input' => "Budi\n15\n",
                'expected_output' => 'Halo, Budi! Kamu berumur 15 tahun.',
                'hidden' => false,
            ],
            [
                'id' => 'tc2',
                'input' => "Siti\n20\n",
                'expected_output' => 'Halo, Siti! Kamu berumur 20 tahun.',
                'hidden' => true,
            ],
        ]);
    }

    private function seedModule2(Course $course): void
    {
        $module = $course->modules()->create([
            'title' => 'Operator & Ekspresi',
            'slug' => 'operator-dan-ekspresi',
            'description' => 'Operator aritmatika, perbandingan, dan logika.',
            'sort_order' => 2,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Operator Aritmatika',
            'slug' => 'operator-aritmatika',
            'description' => 'Penjumlahan, pengurangan, perkalian, pembagian, dll.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
Python mendukung operator aritmatika standar:

| Operator | Operasi | Contoh | Hasil |
|----------|---------|--------|-------|
| `+` | Penjumlahan | `5 + 3` | `8` |
| `-` | Pengurangan | `5 - 3` | `2` |
| `*` | Perkalian | `5 * 3` | `15` |
| `/` | Pembagian | `10 / 3` | `3.333...` |
| `//` | Pembagian bulat | `10 // 3` | `3` |
| `%` | Modulo (sisa) | `10 % 3` | `1` |
| `**` | Pangkat | `2 ** 3` | `8` |
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
a = 10
b = 3

print(a + b)
print(a - b)
print(a * b)
print(a / b)
print(a // b)
print(a % b)
print(a ** b)
PYTHON);

        $this->mcqSingle($lesson, 3, 'Apa hasil dari `17 % 5`?', null, [
            ['id' => 'a', 'text' => '3'],
            ['id' => 'b', 'text' => '2'],
            ['id' => 'c', 'text' => '3.4'],
            ['id' => 'd', 'text' => '12'],
        ], 'b');

        $this->codeFill($lesson, 4, <<<'PYTHON'
# Hitung luas persegi panjang
panjang = 10
lebar = 5
luas = {{A}}
print(luas)
PYTHON, [
            ['id' => 'A', 'answer' => 'panjang * lebar'],
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Operator Perbandingan & Logika',
            'slug' => 'operator-perbandingan-logika',
            'description' => 'Membandingkan nilai dan menggabungkan kondisi.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
**Operator perbandingan** menghasilkan `True` atau `False`:

- `==` sama dengan
- `!=` tidak sama dengan
- `>`, `<`, `>=`, `<=`

**Operator logika** menggabungkan kondisi:

- `and` — keduanya harus True
- `or` — salah satu True
- `not` — kebalikan
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
age = 17
has_id = True

print(age >= 17)
print(age >= 18)
print(age >= 17 and has_id)
print(age >= 18 or has_id)
print(not has_id)
PYTHON);

        $this->mcqMultiple($lesson, 3, 'Ekspresi mana yang menghasilkan `True`?', <<<'PYTHON'
x = 5
y = 10
PYTHON, [
            ['id' => 'a', 'text' => 'x < y'],
            ['id' => 'b', 'text' => 'x == y'],
            ['id' => 'c', 'text' => 'x != y'],
            ['id' => 'd', 'text' => 'x > y'],
        ], ['a', 'c']);
    }

    private function seedModule3(Course $course): void
    {
        $module = $course->modules()->create([
            'title' => 'Control Flow',
            'slug' => 'control-flow',
            'description' => 'Pengambilan keputusan dan perulangan.',
            'sort_order' => 3,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'If / Elif / Else',
            'slug' => 'if-elif-else',
            'description' => 'Membuat keputusan berdasarkan kondisi.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
Statement `if` menjalankan kode **hanya jika kondisi True**. `elif` untuk kondisi tambahan, `else` untuk sisanya.

```python
if kondisi1:
    # jalankan jika kondisi1 True
elif kondisi2:
    # jalankan jika kondisi2 True
else:
    # jalankan jika semua False
```

> **Penting**: Perhatikan indentasi (4 spasi) — Python menggunakan indentasi untuk blok kode.
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
score = 75

if score >= 90:
    print("Grade A")
elif score >= 80:
    print("Grade B")
elif score >= 70:
    print("Grade C")
else:
    print("Grade D")
PYTHON);

        $this->codeReorder($lesson, 3, [
            'score = 85',
            'if score >= 80:',
            '    print("Lulus")',
            'else:',
            '    print("Tidak lulus")',
        ], [0, 1, 2, 3, 4]);

        $this->mcqSingle($lesson, 4, 'Jika `score = 60`, apa output kode di atas?', <<<'PYTHON'
score = 60

if score >= 80:
    print("Lulus")
else:
    print("Tidak lulus")
PYTHON, [
            ['id' => 'a', 'text' => 'Lulus'],
            ['id' => 'b', 'text' => 'Tidak lulus'],
            ['id' => 'c', 'text' => 'Tidak ada output'],
            ['id' => 'd', 'text' => 'Error'],
        ], 'b');

        $lesson = $module->lessons()->create([
            'title' => 'For Loop',
            'slug' => 'for-loop',
            'description' => 'Mengulang kode sejumlah tertentu.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
`for` loop mengulang kode untuk setiap item dalam sebuah urutan (seperti `range()`).

```python
for i in range(5):
    print(i)
```

`range(5)` menghasilkan `0, 1, 2, 3, 4` (tidak termasuk 5).
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
for i in range(5):
    print("Iterasi ke-", i)

print("---")

for i in range(1, 4):
    print(i)
PYTHON);

        $this->codeChallenge($lesson, 3, <<<'MD'
Cetak **jumlah dari 1 sampai 10** (inklusif). Output harus `55`.
MD, <<<'PYTHON'
# Tulis kode di sini
# Gunakan for loop dan range()
PYTHON, [
            [
                'id' => 'tc1',
                'input' => '',
                'expected_output' => '55',
                'hidden' => false,
            ],
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'While Loop',
            'slug' => 'while-loop',
            'description' => 'Mengulang selama kondisi True.',
            'sort_order' => 3,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
`while` loop terus berjalan **selama kondisi True**. Pastikan kondisi akhirnya menjadi False, kalau tidak akan loop selamanya (infinite loop).
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
count = 1

while count <= 5:
    print("Count:", count)
    count = count + 1

print("Selesai!")
PYTHON);

        $this->mcqSingle($lesson, 3, 'Berapa kali "Hello" dicetak?', <<<'PYTHON'
i = 0
while i < 3:
    print("Hello")
    i = i + 1
PYTHON, [
            ['id' => 'a', 'text' => '2'],
            ['id' => 'b', 'text' => '3'],
            ['id' => 'c', 'text' => '4'],
            ['id' => 'd', 'text' => 'Infinite loop'],
        ], 'b');

        $this->codeFill($lesson, 4, <<<'PYTHON'
# Cetak angka 10 sampai 1 (mundur)
i = 10
while i {{A}} 0:
    print(i)
    i = i - 1
PYTHON, [
            ['id' => 'A', 'answer' => '>='],
        ]);
    }

    private function seedModule4(Course $course): void
    {
        $module = $course->modules()->create([
            'title' => 'Fungsi',
            'slug' => 'fungsi',
            'description' => 'Membuat kode reusable dengan fungsi.',
            'sort_order' => 4,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Dasar Fungsi',
            'slug' => 'dasar-fungsi',
            'description' => 'Mendefinisikan dan memanggil fungsi.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
Fungsi adalah **blok kode reusable**. Didefinisikan dengan `def`:

```python
def nama_fungsi():
    # kode di sini
```

Memanggil fungsi cukup tulis namanya: `nama_fungsi()`
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
def greet():
    print("Hello, World!")

greet()
greet()
PYTHON);

        $this->codeReorder($lesson, 3, [
            'def say_hello(name):',
            '    print("Hello, " + name)',
            '',
            'say_hello("Budi")',
        ], [0, 1, 2, 3]);

        $lesson = $module->lessons()->create([
            'title' => 'Parameter & Return',
            'slug' => 'parameter-dan-return',
            'description' => 'Fungsi dengan input dan output.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
**Parameter** adalah input ke fungsi. **Return** adalah output dari fungsi.

```python
def add(a, b):
    return a + b

hasil = add(3, 5)  # hasil = 8
```
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
def luas_persegi(sisi):
    return sisi * sisi

luas = luas_persegi(5)
print(luas)
PYTHON);

        $this->codeChallenge($lesson, 3, <<<'MD'
Buat fungsi `max_of_two(a, b)` yang **mengembalikan** angka yang lebih besar dari `a` dan `b`. Lalu panggil dengan `max_of_two(10, 20)` dan cetak hasilnya.

Output harus `20`.
MD, <<<'PYTHON'
def max_of_two(a, b):
    # lengkapi fungsi ini
    pass

# cetak hasil max_of_two(10, 20)
PYTHON, [
            [
                'id' => 'tc1',
                'input' => '',
                'expected_output' => '20',
                'hidden' => false,
            ],
            [
                'id' => 'tc2',
                'input' => '',
                'expected_output' => '100',
                'hidden' => true,
            ],
        ]);
    }

    private function seedModule5(Course $course): void
    {
        $module = $course->modules()->create([
            'title' => 'Struktur Data',
            'slug' => 'struktur-data',
            'description' => 'List, tuple, set, dan dictionary.',
            'sort_order' => 5,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'List',
            'slug' => 'list',
            'description' => 'Menyimpan banyak nilai dalam urutan.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
**List** menyimpan banyak nilai dalam urutan, pakai kurung siku `[]`. Index dimulai dari 0.

```python
buah = ["apel", "mangga", "jeruk"]
print(buah[0])   # apel
print(buah[1])   # mangga
```

Method penting: `.append()`, `.remove()`, `.pop()`, `len()`.
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
buah = ["apel", "mangga", "jeruk"]

print(buah[0])
print(buah[-1])

buah.append("anggur")
print(buah)

print(len(buah))
PYTHON);

        $this->mcqSingle($lesson, 3, 'Apa output `len([1, 2, 3, 4, 5])`?', null, [
            ['id' => 'a', 'text' => '4'],
            ['id' => 'b', 'text' => '5'],
            ['id' => 'c', 'text' => '6'],
            ['id' => 'd', 'text' => 'Error'],
        ], 'b');

        $this->codeFill($lesson, 4, <<<'PYTHON'
angka = [10, 20, 30, 40, 50]

# Ambil elemen pertama
pertama = {{A}}

# Ambil elemen terakhir
terakhir = {{B}}

print(pertama)
print(terakhir)
PYTHON, [
            ['id' => 'A', 'answer' => 'angka[0]'],
            ['id' => 'B', 'answer' => 'angka[-1]'],
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Tuple & Set',
            'slug' => 'tuple-dan-set',
            'description' => 'Struktur data immutable dan unik.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
**Tuple** seperti list tapi **tidak bisa diubah** (immutable). Pakai kurung biasa `()`.

**Set** menyimpan nilai **unik** (tanpa duplikat). Pakai kurung kurawal `{}`.

```python
koordinat = (3, 5)        # tuple
unik = {1, 2, 3, 2, 1}   # set → {1, 2, 3}
```
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
koordinat = (3, 5)
print(koordinat[0])
print(koordinat[1])

unik = {1, 2, 3, 2, 1, 3}
print(unik)
PYTHON);

        $this->mcqMultiple($lesson, 3, 'Pernyataan mana yang benar tentang tuple?', null, [
            ['id' => 'a', 'text' => 'Tuple bisa diubah setelah dibuat'],
            ['id' => 'b', 'text' => 'Tuple tidak bisa diubah setelah dibuat'],
            ['id' => 'c', 'text' => 'Tuple pakai kurung biasa ()'],
            ['id' => 'd', 'text' => 'Tuple bisa menyimpan berbagai tipe data'],
        ], ['b', 'c', 'd']);

        $lesson = $module->lessons()->create([
            'title' => 'Dictionary',
            'slug' => 'dictionary',
            'description' => 'Menyimpan data dengan key-value pair.',
            'sort_order' => 3,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
**Dictionary** menyimpan pasangan **key-value**. Pakai kurung kurawal `{}` dengan `key: value`.

```python
siswa = {
    "nama": "Budi",
    "umur": 15,
    "kelas": "X-1"
}

print(siswa["nama"])   # Budi
```
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
siswa = {
    "nama": "Budi",
    "umur": 15,
    "kelas": "X-1"
}

print(siswa["nama"])
print(siswa["umur"])

siswa["umur"] = 16
print(siswa["umur"])
PYTHON);

        $this->codeChallenge($lesson, 3, <<<'MD'
Buat dictionary `mahasiswa` dengan key `"nama"` berisi `"Andi"` dan key `"nilai"` berisi `90`. Lalu cetak nilai mahasiswa dengan format:

```
Andi mendapat nilai 90
```
MD, <<<'PYTHON'
# Tulis kode di sini
mahasiswa = {}
PYTHON, [
            [
                'id' => 'tc1',
                'input' => '',
                'expected_output' => 'Andi mendapat nilai 90',
                'hidden' => false,
            ],
        ]);
    }

    private function seedModule6(Course $course): void
    {
        $module = $course->modules()->create([
            'title' => 'String Manipulation',
            'slug' => 'string-manipulation',
            'description' => 'Method dan formatting string.',
            'sort_order' => 6,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'String Methods',
            'slug' => 'string-methods',
            'description' => 'Method built-in untuk string.',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
String punya banyak **method** built-in:

| Method | Fungsi | Contoh |
|--------|--------|--------|
| `.upper()` | Huruf kapital semua | `"abc".upper()` → `"ABC"` |
| `.lower()` | Huruf kecil semua | `"ABC".lower()` → `"abc"` |
| `.strip()` | Hapus spasi awal/akhir | `" hi ".strip()` → `"hi"` |
| `.split()` | Pisah jadi list | `"a,b".split(",")` → `["a","b"]` |
| `.replace()` | Ganti teks | `"hi".replace("i","ey")` → `"hey"` |
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
nama = "  Budi Santoso  "

print(nama.strip())
print(nama.upper())
print(nama.lower())
print(nama.strip().replace("Budi", "Andi"))
PYTHON);

        $this->codeReorder($lesson, 3, [
            'kalimat = "Hello World"',
            'kata = kalimat.split()',
            'print(kata)',
        ], [0, 1, 2]);

        $this->mcqSingle($lesson, 4, 'Apa output `"Python".lower()`?', null, [
            ['id' => 'a', 'text' => 'PYTHON'],
            ['id' => 'b', 'text' => 'python'],
            ['id' => 'c', 'text' => 'Python'],
            ['id' => 'd', 'text' => 'Error'],
        ], 'b');

        $lesson = $module->lessons()->create([
            'title' => 'String Formatting',
            'slug' => 'string-formatting',
            'description' => 'f-string dan concatenation.',
            'sort_order' => 2,
            'is_published' => true,
        ]);

        $this->text($lesson, 1, <<<'MD'
**f-string** adalah cara modern untuk memformat string di Python (Python 3.6+):

```python
nama = "Budi"
umur = 15
print(f"Halo, {nama}! Umur kamu {umur} tahun.")
```

Bisa juga pakai **concatenation** dengan `+`, tapi f-string lebih readable.
MD);

        $this->codeExample($lesson, 2, <<<'PYTHON'
nama = "Budi"
umur = 15

print("Halo, " + nama + "!")
print(f"Halo, {nama}! Umur kamu {umur} tahun.")
print(f"Tahun depan umur kamu {umur + 1} tahun.")
PYTHON);

        $this->codeFill($lesson, 3, <<<'PYTHON'
nama = "Siti"
nilai = 95

# Gunakan f-string untuk mencetak:
# "Siti mendapat nilai 95"
hasil = {{A}}
print(hasil)
PYTHON, [
            ['id' => 'A', 'answer' => 'f"{nama} mendapat nilai {nilai}"'],
        ]);

        $this->codeChallenge($lesson, 4, <<<'MD'
Buat program yang meminta input **nama** dan **nilai** (angka), lalu cetak dengan f-string:

```
[nama] lulus dengan nilai [nilai]!
```
MD, <<<'PYTHON'
# Tulis kode di sini
nama = input()
nilai = input()
PYTHON, [
            [
                'id' => 'tc1',
                'input' => "Andi\n85\n",
                'expected_output' => 'Andi lulus dengan nilai 85!',
                'hidden' => false,
            ],
            [
                'id' => 'tc2',
                'input' => "Rina\n100\n",
                'expected_output' => 'Rina lulus dengan nilai 100!',
                'hidden' => true,
            ],
        ]);
    }

    private function text(Lesson $lesson, int $sort, string $markdown): void
    {
        $lesson->blocks()->create([
            'type' => LessonBlockType::TEXT,
            'content' => ['text' => $markdown],
            'sort_order' => $sort,
        ]);
    }

    private function codeExample(Lesson $lesson, int $sort, string $code, string $language = 'python', string $markdown = ''): void
    {
        $content = ['language' => $language, 'code' => $code];
        if ($markdown !== '') {
            $content['markdown'] = $markdown;
        }

        $lesson->blocks()->create([
            'type' => LessonBlockType::CODE_EXAMPLE,
            'content' => $content,
            'sort_order' => $sort,
        ]);
    }

    private function hint(Lesson $lesson, int $sort, string $title, string $markdown): void
    {
        $lesson->blocks()->create([
            'type' => LessonBlockType::HINT,
            'content' => ['title' => $title, 'text' => $markdown],
            'sort_order' => $sort,
        ]);
    }

    private function mcqSingle(Lesson $lesson, int $sort, string $question, ?string $code, array $options, string $correctAnswer): void
    {
        $content = [
            'question' => $question,
            'options' => $options,
            'correct_answer' => $correctAnswer,
        ];
        if ($code !== null) {
            $content['code'] = $code;
        }

        $lesson->blocks()->create([
            'type' => LessonBlockType::MCQ_SINGLE,
            'content' => $content,
            'sort_order' => $sort,
        ]);
    }

    private function mcqMultiple(Lesson $lesson, int $sort, string $question, ?string $code, array $options, array $correctAnswers): void
    {
        $content = [
            'question' => $question,
            'options' => $options,
            'correct_answers' => $correctAnswers,
        ];
        if ($code !== null) {
            $content['code'] = $code;
        }

        $lesson->blocks()->create([
            'type' => LessonBlockType::MCQ_MULTIPLE,
            'content' => $content,
            'sort_order' => $sort,
        ]);
    }

    private function codeFill(Lesson $lesson, int $sort, string $template, array $blanks): void
    {
        $lesson->blocks()->create([
            'type' => LessonBlockType::CODE_FILL,
            'content' => ['code_template' => $template, 'blanks' => $blanks],
            'sort_order' => $sort,
        ]);
    }

    private function codeReorder(Lesson $lesson, int $sort, array $lines, array $correctOrder): void
    {
        $lesson->blocks()->create([
            'type' => LessonBlockType::CODE_REORDER,
            'content' => ['lines' => $lines, 'correct_order' => $correctOrder],
            'sort_order' => $sort,
        ]);
    }

    private function codeChallenge(Lesson $lesson, int $sort, string $prompt, string $starterCode, array $testcases): void
    {
        $lesson->blocks()->create([
            'type' => LessonBlockType::CODE_CHALLENGE,
            'content' => [
                'prompt' => $prompt,
                'starter_code' => $starterCode,
                'testcases' => $testcases,
            ],
            'sort_order' => $sort,
        ]);
    }
}
