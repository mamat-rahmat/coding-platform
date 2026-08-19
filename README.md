# coding-platform

Platform belajar pemrograman interaktif ala SoloLearn, dibangun dengan
Laravel 13 + Vue 3 + Inertia.js. MVP fokus Python saja dengan eksekusi
kode via Pyodide (WASM di browser).

## Fitur

### Untuk Siswa

- **Course catalog** — daftar course published dengan progress tracking
- **Lesson viewer** — lesson terdiri dari 8 tipe block:
  - `TEXT` — materi teks dengan format markdown
  - `CODE_EXAMPLE` — contoh kode dengan syntax highlighting (CodeMirror 6)
  - `HINT` — petunjuk collapsible
  - `MCQ_SINGLE` — pilihan ganda satu jawaban benar
  - `MCQ_MULTIPLE` — pilihan ganda multi-jawaban benar
  - `CODE_FILL` — lengkapi bagian kosong pada kode
  - `CODE_REORDER` — susun ulang baris kode ke urutan benar
  - `CODE_CHALLENGE` — soal coding dengan testcase & eksekusi Pyodide
- **Auto-completion** — lesson termark selesai otomatis ketika semua
  graded block terjawab benar
- **Playground** — editor Python bebas dengan eksekusi via Pyodide
- **Progress tracking** — progress bar per lesson, per course

### Untuk Admin

- **Admin dashboard** — statistik platform (counts, block type breakdown)
- **CRUD lengkap** untuk Course → Module → Lesson → LessonBlock
- **Per-type block editor** — 8 editor khusus sesuai tipe block
- **Block reordering** — drag-and-drop via vuedraggable
- **Preview as student** — lihat lesson seperti siswa

## Tech Stack

- **Backend**: Laravel 13 (PHP 8.3), Laravel Fortify, Inertia.js,
  Laravel Wayfinder
- **Frontend**: Vue 3, TailwindCSS 4, reka-ui + shadcn-vue, VueUse,
  lucide icons, Vite 8
- **Code editor**: CodeMirror 6 (`vue-codemirror` + `@codemirror/lang-python`)
- **Python runtime**: Pyodide (WASM, dimuat dari CDN jsdelivr dengan
  version pin di `resources/js/composables/usePyodide.ts`)
- **Testing**: Pest 5
- **Static analysis**: PHPStan via Larastan
- **Linters**: Pint (PHP), ESLint + Prettier (JS/TS)

## Setup

```bash
composer setup        # install + migrate + build
composer dev          # server + queue + vite (concurrent)
```

### First-run notes

`composer setup` menjalankan semua langkah yang diperlukan: install
composer + npm deps, generate app key, migrate, dan build Vite assets.

Langkah manual yang kadang perlu dijalankan terpisah:

- `php artisan wayfinder:generate` — generate typed route helpers di
  `resources/js/routes/` & `resources/js/actions/`. Wajib dijalankan
  setelah menambah/mengubah route Laravel.
- `npm run build` — build Vite assets. Wajib sebelum menjalankan
  `php artisan test` agar Inertia pages bisa render.
- `php -d memory_limit=512M vendor/bin/phpstan analyse --memory-limit=512M`
  — PHPStan butuh memory >128MB default.

### Demo Accounts

Setelah `php artisan db:seed`:

| Email | Password | Role |
|---|---|---|
| `admin@example.com` | `password` | Admin |
| `coderbodoh@gmail.com` | `pass123` | Siswa |

## Quality Check Commands

### PHP

```bash
composer lint:check     # Pint check
composer types:check    # PHPStan
```

### Frontend

```bash
npm run lint:check      # ESLint
npm run format:check    # Prettier
npm run types:check     # vue-tsc
```

### Tests

```bash
php artisan test
# atau
composer test           # config:clear + lint:check + types:check + test
```

### CI lengkap

```bash
composer ci:check       # lint + format + types + test
```

## Architecture

### Lesson structure

```
Course -> CourseModule -> Lesson -> LessonBlock
```

`App\LessonBlockType` enum menentukan tipe block. Setiap tipe punya:
- TS interface di `resources/js/types/lesson.ts`
- Vue component di `resources/js/components/lesson/`
- (jika graded) endpoint submit di `BlockAttemptController`
- (admin) editor khusus di `resources/js/components/admin/blockEditors/`

### Authorization

- Role admin via kolom `is_admin` di tabel `users`.
- Gate `admin` didefinisikan di `app/Providers/AppServiceProvider.php`.
- Middleware `EnsureUserIsAdmin` untuk route group admin.
- Policies (`CoursePolicy`, dll) enforce Gate `admin` pada operasi CRUD.

### Code Execution

Pyodide dimuat lazy dari CDN saat pertama kali `loadPyodide()` dipanggil
(Butuh koneksi internet untuk fetch WASM binary ~10MB, di-cache browser
setelahnya). Verifikasi jawaban `CODE_CHALLENGE` dilakukan client-side
(testcase dijalankan di browser); hasil dilaporkan ke server untuk
penyimpanan attempt.

## Useful Files

- `routes/web.php` — route utama
- `routes/admin.php` — route admin group
- `routes/settings.php` — route settings & auth
- `app/Providers/FortifyServiceProvider.php` — view auth
- `database/seeders/DatabaseSeeder.php` — data demo
- `resources/js/app.ts` — entry point Inertia
- `AGENTS.md` — panduan untuk kontributor & AI agent

## License

MIT
