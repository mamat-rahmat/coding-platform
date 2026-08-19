# AGENTS.md

Panduan untuk agent (manusia & AI) yang bekerja di repo ini.

## Project Overview

**coding-platform** adalah platform belajar pemrograman interaktif ala SoloLearn,
dibangun dengan Laravel 13 + Vue 3 + Inertia.js. MVP fokus Python saja dengan
eksekusi kode via Pyodide (WASM di browser).

- Bahasa konten: Bahasa Indonesia
- Target platform: Desktop-first
- DB: SQLite (default), sesi/antrian/cache pakai database

## Tech Stack

- **Backend**: Laravel 13 (PHP 8.3), Laravel Fortify, Inertia.js, Laravel Wayfinder
- **Frontend**: Vue 3, TailwindCSS 4, reka-ui + shadcn-vue, VueUse, lucide icons, Vite 8
- **Testing**: Pest 5
- **Static analysis**: PHPStan via Larastan
- **Linters**: Pint (PHP), ESLint + Prettier (JS/TS)

## Setup

```bash
composer setup        # install + migrate + build
composer dev          # server + queue + vite (concurrent)
```

## Commit Policy

- **Granularity**: commit per sub-task lengkap (bukan per file, bukan per fase).
- **Branch**: langsung ke `main`.
- **Style**: Conventional Commits (`feat:`, `fix:`, `refactor:`, `chore:`,
  `docs:`, `test:`, `style:`, `perf:`).
- **Sebelum commit**: jalankan quality check yang relevan (lihat bawah).
- **Jangan commit secrets** — verifikasi `.env` tidak di-stage.
- **Jangan amend failed commit** — buat commit baru setelah fix.
- **Stage hanya file yang relevan** — jangan `git add -A` sembarangan.

## Quality Check Commands

Jalankan sebelum commit, sesuai scope perubahan:

### PHP (jika edit file .php)

```bash
composer lint:check     # Pint check (tidak rewrite)
composer types:check    # PHPStan
```

### Frontend (jika edit file .ts/.vue/.js/.css di resources/)

```bash
npm run lint:check      # ESLint check
npm run format:check    # Prettier check
npm run types:check     # vue-tsc --noEmit
```

### Tests (jika edit logic / tambah fitur)

```bash
php artisan test        # Pest feature + unit
# atau
composer test           # config:clear + lint:check + types:check + test
```

### CI lengkap (sebelum push / PR)

```bash
composer ci:check       # lint + format + types + test
```

## Code Conventions

- **Jangan tambahkan komentar** kecuali diminta explicit oleh user.
- Ikuti konvensi styling yang sudah ada (Pint + Prettier handle mayoritas).
- Model: gunakan PHP 8 attributes (`#[Fillable(...)]`, `#[Hidden(...)]`) seperti
  pola di `app/Models/User.php`.
- Controller: type-hint return type (`Response`, `RedirectResponse`).
- Frontend: gunakan komponen shadcn-vue yang sudah ada di
  `resources/js/components/ui/` sebanyak mungkin.
- Routes: pakai helper Wayfinder (`@/routes/*`) di frontend, jangan hardcode
  URL string.
- Database: gunakan migration + factory + seeder untuk semua perubahan schema.

## Architecture Notes

### Lesson structure

```
Course -> CourseModule -> Lesson -> LessonBlock
```

`App\LessonBlockType` enum menentukan tipe block. Setiap tipe punya:
- TS interface di `resources/js/types/lesson.ts`
- Vue component di `resources/js/components/lesson/`
- (jika perlu) endpoint submit di `BlockAttemptController`
- (admin) editor khusus di `resources/js/components/admin/blockEditors/`

### Authorization

- Role admin via kolom `is_admin` di tabel `users`.
- Gate `admin` didefinisikan di `app/Providers/AppServiceProvider.php`.
- Middleware `EnsureUserIsAdmin` untuk route group admin.

## Useful Files

- `routes/web.php` — route utama
- `routes/settings.php` — route settings & auth
- `app/Providers/FortifyServiceProvider.php` — view auth
- `database/seeders/DatabaseSeeder.php` — data demo
- `resources/js/app.ts` — entry point Inertia
