# System Design Document (SDD)
## coding-platform — Platform Belajar Pemrograman Interaktif

---

## 1. Document Information

### 1.1 Document Purpose
Dokumen ini menjelaskan arsitektur teknis, komponen, data model, dan
keputusan desain untuk platform **coding-platform**. Dokumen menjadi
acuan teknis bagi developer untuk memahami struktur sistem dan
menambahkan fitur baru dengan konsisten.

### 1.2 Version History

| Versi | Tanggal       | Deskripsi                          | Penulis      |
|-------|---------------|------------------------------------|--------------|
| 1.0   | 19 Agt 2026   | Dokumen awal untuk MVP Python-only | Tim Engineering |

### 1.3 Document Owner
Tim Engineering coding-platform.

---

## 2. System Overview

### 2.1 Deskripsi Sistem
Web application monolit berbasis Laravel + Vue 3 + Inertia.js. Siswa
belajar Python melalui lesson bite-sized yang terdiri dari 8 tipe block
interaktif. Eksekusi kode Python dilakukan **di browser** via Pyodide
(WASM) — server tidak menjalankan kode user. Admin mengelola konten
melalui panel admin terpisah.

### 2.2 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                       Browser (Client)                       │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐  │
│  │  Vue 3 +     │  │  Inertia.js  │  │  Pyodide (WASM)  │  │
│  │  TailwindCSS │  │  (SPA-like)  │  │  Python runtime   │  │
│  │  shadcn-vue  │  │              │  │  dari CDN jsdelivr│  │
│  └──────────────┘  └──────────────┘  └──────────────────┘  │
│         │                  │                  ▲             │
│         │                  │                  │             │
│         ▼                  ▼                  │             │
└─────────┼──────────────────┼──────────────────┼─────────────┘
          │ HTTP (JSON)      │                  │ fetch WASM
          ▼                  │                  │
┌─────────────────────────┐  │                  │
│   Laravel 13 (Server)   │  │                  │
│                         │  │                  │
│  ┌───────────────────┐  │  │                  │
│  │  Controllers      │  │                  │
│  │  - Course         │  │                  │
│  │  - Lesson         │  │                  │
│  │  - BlockAttempt   │  │                  │
│  │  - Playground     │  │                  │
│  │  - Admin*         │  │                  │
│  └───────────────────┘  │                  │
│  ┌───────────────────┐  │                  │
│  │  Policies + Gate  │  │                  │
│  │  (admin role)     │  │                  │
│  └───────────────────┘  │                  │
│  ┌───────────────────┐  │                  │
│  │  Eloquent Models  │  │                  │
│  └───────────────────┘  │                  │
│  ┌───────────────────┐  │                  │
│  │  SQLite (file)    │  │                  │
│  └───────────────────┘  │                  │
└─────────────────────────┘                  │
                                               │
                          ┌──────────────────┴──────────┐
                          │   CDN jsdelivr (Pyodide)    │
                          │   ~10MB, di-cache browser   │
                          └─────────────────────────────┘
```

### 2.3 Tech Stack

| Layer           | Teknologi                                            |
|-----------------|------------------------------------------------------|
| Backend         | Laravel 13 (PHP 8.3), Laravel Fortify, Inertia.js    |
| Frontend        | Vue 3, TailwindCSS 4, reka-ui + shadcn-vue, VueUse   |
| Code Editor     | CodeMirror 6 (`vue-codemirror`, `@codemirror/lang-python`) |
| Python Runtime  | Pyodide (WASM, CDN jsdelivr dengan version pin)      |
| Route Helpers   | Laravel Wayfinder (typed, generated)                 |
| Database        | SQLite (default), sesi/antrian/cache pakai database  |
| Build Tool      | Vite 8                                               |
| Testing         | Pest 5                                               |
| Static Analysis | PHPStan via Larastan                                 |
| Linters         | Pint (PHP), ESLint + Prettier (JS/TS)                |

---

## 3. Component Architecture

### 3.1 Backend Components

#### 3.1.1 Controllers

**Siswa-side:**

| Controller                  | Responsibility                              |
|-----------------------------|---------------------------------------------|
| `CourseController`          | Index & show course (hanya published)       |
| `LessonController`          | Show lesson + per-block attempt status      |
| `LessonProgressController`  | Mark lesson complete (manual fallback)      |
| `BlockAttemptController`    | Submit jawaban graded block + auto-complete |
| `PlaygroundController`      | Render halaman playground                   |

**Admin-side:**

| Controller                    | Responsibility                                |
|-------------------------------|-----------------------------------------------|
| `AdminController`             | Dashboard statistik                           |
| `AdminCourseController`       | CRUD course                                   |
| `AdminModuleController`       | CRUD module (nested under course, shallow)    |
| `AdminLessonController`       | CRUD lesson (nested under module, shallow)    |
| `AdminLessonBlockController`  | CRUD block + reorder endpoint                 |

#### 3.1.2 Middleware

| Middleware             | Purpose                                       |
|------------------------|-----------------------------------------------|
| `HandleAppearance`     | Share appearance cookie ke view              |
| `HandleInertiaRequests`| Share props default Inertia (auth, sidebar)  |
| `EnsureUserIsAdmin`    | Block non-admin dari route admin (403)        |
| Laravel Fortify        | Auth, rate limiting, password reset           |

#### 3.1.3 Authorization

```
Gate::define('admin', fn(User $u) => $u->is_admin)
        │
        ▼
Policies (auto-discovered):
├── CoursePolicy
├── CourseModulePolicy
├── LessonPolicy
└── LessonBlockPolicy
```

Semua policy method (`viewAny`, `view`, `create`, `update`, `delete`)
memeriksa `$user->is_admin`. Controllers memanggil `$this->authorize()`
untuk enforce.

#### 3.1.4 Form Requests (Admin)

```
app/Http/Requests/Admin/
├── StoreCourseRequest.php
├── UpdateCourseRequest.php
├── StoreModuleRequest.php
├── UpdateModuleRequest.php
├── StoreLessonRequest.php
├── UpdateLessonRequest.php
├── StoreLessonBlockRequest.php
├── UpdateLessonBlockRequest.php
└── ReorderLessonBlocksRequest.php
```

Setiap FormRequest memvalidasi field wajib + unique slug + enum `type`
untuk LessonBlock.

### 3.2 Frontend Components

#### 3.2.1 Layouts

```
resources/js/layouts/
├── AppLayout.vue              # Layout siswa (sidebar)
├── AuthLayout.vue             # Layout auth (login/register)
├── settings/Layout.vue        # Layout settings (nested)
└── admin/
    ├── AdminLayout.vue         # Wrapper
    └── AdminSidebarLayout.vue  # Layout admin (sidebar berbeda)
```

Layout dipilih di `resources/js/app.ts` berdasarkan nama page:
- `Welcome` → no layout
- `auth/*` → AuthLayout
- `settings/*` → [AppLayout, SettingsLayout]
- `admin/*` → AdminLayout
- default → AppLayout

#### 3.2.2 Lesson Block Components

```
resources/js/components/lesson/
├── LessonBlockRenderer.vue    # Dispatcher berdasarkan block.type
├── TextBlock.vue              # TEXT (markdown)
├── CodeExampleBlock.vue       # CODE_EXAMPLE (CodeMirror readonly)
├── HintBlock.vue              # HINT (collapsible)
├── McqSingleBlock.vue         # MCQ_SINGLE (radio)
├── McqMultipleBlock.vue       # MCQ_MULTIPLE (checkbox)
├── CodeFillBlock.vue          # CODE_FILL (input per blank)
├── CodeReorderBlock.vue       # CODE_REORDER (vuedraggable)
└── CodeChallengeBlock.vue     # CODE_CHALLENGE (editor + Pyodide + testcases)
```

`LessonBlockRenderer` menerima prop `block: LessonBlock` (discriminated
union dari `types/lesson.ts`) dan render komponen yang sesuai.

#### 3.2.3 Admin Block Editors

```
resources/js/components/admin/
├── AdminSidebar.vue
├── BlockEditorDispatcher.vue   # Dispatcher berdasarkan type
└── blockEditors/
    ├── TextBlockEditor.vue
    ├── CodeExampleBlockEditor.vue
    ├── HintBlockEditor.vue
    ├── McqSingleBlockEditor.vue
    ├── McqMultipleBlockEditor.vue
    ├── CodeFillBlockEditor.vue
    ├── CodeReorderBlockEditor.vue
    └── CodeChallengeBlockEditor.vue
```

Setiap editor pakai `defineModel<ContentType>()` untuk two-way binding
dengan parent. Dispatcher menerima `type` + `v-model` content.

#### 3.2.4 Shared Components

| Komponen         | Purpose                                        |
|------------------|------------------------------------------------|
| `CodeEditor.vue` | Wrapper CodeMirror 6 (props: modelValue, readonly, language) |
| `Markdown.vue`   | Render markdown via `marked` + sanitasi dasar  |

### 3.3 Composables

```
resources/js/composables/
├── useAppearance.ts     # Theme light/dark/system
├── useCurrentUrl.ts     # URL-aware navigation
├── useInitials.ts       # Avatar initials
└── usePyodide.ts        # Singleton Pyodide loader + runCode API
```

`usePyodide` adalah singleton module-level: `loadPyodide()` dipanggil
sekali, instance reuse lintas komponen. API:
- `loadPyodide(): Promise<void>` — lazy-load dari CDN
- `runCode(code, stdin?): Promise<RunResult>` — eksekusi + capture stdout/stderr
- State: `pyodideReady`, `pyodideLoading`, `pyodideError`

---

## 4. Data Model

### 4.1 Entity Relationship Diagram

```
┌──────────┐     ┌───────────────┐     ┌─────────┐     ┌──────────────┐
│  users   │     │    courses    │     │ course_ │     │   lessons    │
│──────────│     │───────────────│     │ modules │     │──────────────│
│ id       │     │ id            │◄────│ id      │◄────│ id           │
│ name     │     │ title         │     │ course_ │     │ course_      │
│ email    │     │ slug          │     │  id (FK)│     │  module_id FK│
│ password │     │ language      │     │ title   │     │ title        │
│ is_admin │     │ level         │     │ slug    │     │ slug         │
│ ...      │     │ xp_reward     │     │ sort_   │     │ sort_order   │
└────┬─────┘     │ is_published  │     │  order  │     │ is_published │
     │           └───────────────┘     └─────────┘     └──────┬───────┘
     │                                                  │
     │           ┌──────────────────┐                   │
     │           │  lesson_blocks   │◄──────────────────┘
     │           │──────────────────│
     │           │ id               │
     │           │ lesson_id (FK)   │
     │           │ type (enum)      │
     │           │ content (JSON)   │
     │           │ sort_order       │
     │           └────────┬─────────┘
     │                    │
     │           ┌────────┴─────────┐
     │           │ block_attempts   │
     │           │──────────────────│
     ├──────────►│ user_id (FK)     │
     │           │ lesson_block_id  │
     │           │ selected_answer  │
     │           │ is_correct       │
     │           │ attempt_data JSON│
     │           │ score            │
     │           │ answered_at      │
     │           └──────────────────┘
     │
     │           ┌──────────────────┐
     │           │ lesson_progress  │
     │           │──────────────────│
     └──────────►│ user_id (FK)     │
                 │ lesson_id (FK)   │
                 │ completed_at     │
                 └──────────────────┘
```

### 4.2 Tabel & Kolom Utama

#### users
| Kolom              | Tipe      | Catatan                          |
|--------------------|-----------|----------------------------------|
| id                 | bigint    | PK                               |
| name               | string    |                                  |
| email              | string    | unique                           |
| password           | string    | hashed                           |
| is_admin           | boolean   | default false                    |
| email_verified_at  | timestamp | nullable                         |

#### courses
| Kolom          | Tipe         | Catatan                  |
|----------------|--------------|--------------------------|
| id             | bigint       | PK                       |
| title          | string       |                          |
| slug           | string       | unique                   |
| description    | text         | nullable                 |
| language       | string(50)   | default 'python'         |
| level          | string(50)   | default 'beginner'       |
| xp_reward      | uint         | default 0                |
| is_published   | boolean      | default false            |

#### course_modules
| Kolom       | Tipe   | Catatan                          |
|-------------|--------|----------------------------------|
| id          | bigint | PK                               |
| course_id   | FK     | cascade delete                   |
| title       | string |                                  |
| slug        | string | unique per (course_id, slug)     |
| sort_order  | uint   |                                  |

#### lessons
| Kolom             | Tipe    | Catatan                            |
|-------------------|---------|------------------------------------|
| id                | bigint  | PK                                 |
| course_module_id  | FK      | cascade delete                     |
| title             | string  |                                    |
| slug              | string  | unique per (course_module_id, slug)|
| sort_order        | uint    |                                    |
| is_published      | boolean | default false                      |

#### lesson_blocks
| Kolom       | Tipe   | Catatan                               |
|-------------|--------|---------------------------------------|
| id          | bigint | PK                                    |
| lesson_id   | FK     | cascade delete                        |
| type        | enum   | `App\LessonBlockType` (8 nilai)       |
| content     | json   | schema berbeda per tipe (lihat 4.3)   |
| sort_order  | uint   |                                       |

#### block_attempts
| Kolom             | Tipe      | Catatan                                |
|-------------------|-----------|----------------------------------------|
| id                | bigint    | PK                                     |
| user_id           | FK        | cascade delete                         |
| lesson_block_id   | FK        | cascade delete                         |
| selected_answer   | string    |                                        |
| is_correct        | boolean   |                                        |
| attempt_data      | json      | nullable (hasil per testcase, dll)     |
| score             | tinyint   | nullable (0-100)                       |
| answered_at       | timestamp |                                        |

#### lesson_progress
| Kolom         | Tipe      | Catatan                          |
|---------------|-----------|----------------------------------|
| id            | bigint    | PK                               |
| user_id       | FK        | cascade delete                   |
| lesson_id     | FK        | cascade delete                   |
| completed_at  | timestamp | nullable                         |
|               |           | unique (user_id, lesson_id)      |

### 4.3 Content Schema per Block Type

Kolom `lesson_blocks.content` (JSON) punya schema berbeda per tipe:

| Tipe             | Schema                                                          |
|------------------|-----------------------------------------------------------------|
| `TEXT`           | `{ text: string (markdown) }`                                  |
| `CODE_EXAMPLE`   | `{ language: string, code: string }`                            |
| `HINT`           | `{ title: string, text: string (markdown) }`                    |
| `MCQ_SINGLE`     | `{ question, code?, options:[{id,text}], correct_answer: id }`  |
| `MCQ_MULTIPLE`   | `{ question, code?, options:[{id,text}], correct_answers: [id] }` |
| `CODE_FILL`      | `{ code_template: string, blanks:[{id,answer,alternatives?}] }`|
| `CODE_REORDER`   | `{ lines:[string], correct_order:[int] }`                       |
| `CODE_CHALLENGE` | `{ prompt, starter_code, testcases:[{id,input,expected_output,hidden}], time_limit_ms? }` |

### 4.4 Enum: LessonBlockType

```php
enum LessonBlockType: string {
    case TEXT = 'TEXT';
    case CODE_EXAMPLE = 'CODE_EXAMPLE';
    case HINT = 'HINT';
    case MCQ_SINGLE = 'MCQ_SINGLE';
    case MCQ_MULTIPLE = 'MCQ_MULTIPLE';
    case CODE_FILL = 'CODE_FILL';
    case CODE_REORDER = 'CODE_REORDER';
    case CODE_CHALLENGE = 'CODE_CHALLENGE';
}
```

**Graded** (menyimpan attempt): `MCQ_SINGLE`, `MCQ_MULTIPLE`,
`CODE_FILL`, `CODE_REORDER`, `CODE_CHALLENGE`.
**Non-graded**: `TEXT`, `CODE_EXAMPLE`, `HINT`.

---

## 5. Application Flow

### 5.1 Lesson Viewing & Answer Submission

```
Siswa buka /lessons/{slug}
        │
        ▼
LessonController@show
        │
        ├─ Load lesson + blocks + module.course
        ├─ Load latest attempt per graded block untuk user
        ├─ Attach is_answered/is_correct ke setiap block
        ├─ Hitung blockStatus {totalGraded, correctGraded, allCorrect}
        │
        ▼
Render Lessons/Show.vue
        │
        ▼
Loop blocks → LessonBlockRenderer dispatch per tipe
        │
        ▼
User interaksi (pilih jawaban / tulis kode / run)
        │
        ▼
Submit → POST /lesson-blocks/{block}/attempts
        │
        ▼
BlockAttemptController@store
        │
        ├─ Dispatch verify per tipe (match)
        │   ├─ MCQ_SINGLE/MCQ_MULTIPLE/CODE_REORDER: server compare
        │   └─ CODE_FILL/CODE_CHALLENGE: pakai is_correct dari client
        ├─ Simpan BlockAttempt
        ├─ Jika is_correct → maybeCompleteLesson()
        │   ├─ Cek semua graded block punya correct attempt
        │   └─ Jika ya → updateOrCreate LessonProgress(completed_at: now)
        │
        ▼
Return back() with flash attempt_result
        │
        ▼
Frontend update UI (warna hijau/merah, progress bar)
```

### 5.2 Code Challenge Execution (Pyodide)

```
CodeChallengeBlock.vue
        │
        ├─ User tulis kode di CodeEditor
        ├─ Klik "Run" → runCode(code, stdin)
        │   ├─ if !pyodideReady: loadPyodide() dari CDN
        │   ├─ setStdout/setStderr handler
        │   ├─ setStdin handler (line-by-line dari textarea)
        │   ├─ loadPackagesFromImports(code)
        │   └─ runPythonAsync(code)
        ├─ Klik "Run Testcases"
        │   ├─ Loop testcases
        │   │   └─ runCode(code, testcase.input) → compare stdout dengan expected
        │   └─ Simpan hasil per testcase ke testcaseResults
        ├─ Klik "Submit Hasil"
        │   └─ POST /lesson-blocks/{block}/attempts
        │       { answer: "x/y", is_correct, attempt_data, score }
        │
        ▼
Server simpan BlockAttempt + auto-complete lesson jika semua benar
```

### 5.3 Admin Block Reordering

```
Admin buka /admin/lessons/{lesson}/blocks
        │
        ▼
blocks/Index.vue (vuedraggable)
        │
        ├─ Drag-and-drop blocks
        ├─ onDragEnd → update sort_order locally, isDirty = true
        ├─ Klik "Save Order"
        │   └─ PATCH /admin/lessons/{lesson}/blocks/reorder
        │       { blocks: [{id, sort_order}, ...] }
        │
        ▼
AdminLessonBlockController@reorder
        │
        ├─ Validate (ReorderLessonBlocksRequest)
        ├─ DB::transaction:
        │   └─ Loop blocks → update sort_order WHERE lesson_id = lesson
        └─ back() with success
```

---

## 6. API & Routes

### 6.1 Siswa Routes (auth + verified)

| Method | Path                                       | Controller@action              |
|--------|--------------------------------------------|--------------------------------|
| GET    | `/`                                        | Inertia `Welcome`              |
| GET    | `/dashboard`                               | Inertia `Dashboard`            |
| GET    | `/courses`                                 | CourseController@index         |
| GET    | `/courses/{course:slug}`                   | CourseController@show          |
| GET    | `/lessons/{lesson:slug}`                   | LessonController@show          |
| POST   | `/lessons/{lesson:slug}/complete`          | LessonProgressController@complete |
| POST   | `/lesson-blocks/{lessonBlock}/attempts`    | BlockAttemptController@store   |
| GET    | `/playground`                              | PlaygroundController@index     |

### 6.2 Admin Routes (auth + verified + admin)

| Method   | Path                                          | Controller@action                  |
|----------|-----------------------------------------------|------------------------------------|
| GET      | `/admin`                                      | AdminController@dashboard          |
| GET/POST | `/admin/courses`                              | AdminCourseController@index/store  |
| GET/...  | `/admin/courses/{course}`                     | show/create/edit/update/destroy    |
| GET/POST | `/admin/courses/{course}/modules`             | AdminModuleController@index/store  |
| GET/...  | `/admin/modules/{module}`                     | show/create/edit/update/destroy    |
| GET/POST | `/admin/modules/{module}/lessons`             | AdminLessonController@index/store  |
| GET/...  | `/admin/lessons/{lesson}`                     | show/create/edit/update/destroy    |
| GET/POST | `/admin/lessons/{lesson}/blocks`              | AdminLessonBlockController@index/store |
| GET/...  | `/admin/blocks/{block}`                       | edit/update/destroy (shallow)      |
| PATCH    | `/admin/lessons/{lesson}/blocks/reorder`      | AdminLessonBlockController@reorder |

Catatan: admin routes pakai `Route::resource(...)->shallow()` untuk
module/lesson/block — index/create/store nested, show/edit/update/destroy
flat.

### 6.3 Frontend Route Helpers

Wayfinder generate typed helpers di `resources/js/routes/` dan
`resources/js/actions/`. Frontend **wajib** pakai helper ini, tidak
hardcode URL string.

Contoh pakai:
```ts
import courseRoutes from '@/routes/courses';
import adminCourseRoutes from '@/routes/admin/courses';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

courseRoutes.show.url(course.slug);
adminCourseRoutes.store.url();
attemptRoutes.store.url(blockId);
```

---

## 7. Security Design

### 7.1 Authentication
- Laravel Fortify: register, login, password reset, email verification.
- Password di-hash Bcrypt (rounds 12).
- Rate limiting login: 5 percobaan/menit per IP+email.

### 7.2 Authorization
- Role admin via kolom `is_admin` di `users`.
- Gate `admin` di `AppServiceProvider::boot()`.
- Middleware `EnsureUserIsAdmin` (alias `admin`) untuk route group admin.
- Policies (`CoursePolicy`, dll) enforce Gate pada setiap operasi CRUD.
- Controllers panggil `$this->authorize()` sebelum operasi.

### 7.3 Code Execution Security
- Eksekusi kode Python 100% di browser via Pyodide (WASM sandbox).
- Server **tidak pernah** menjalankan kode user.
- ⚠️ Verifikasi jawaban `CODE_CHALLENGE` & `CODE_FILL` bersifat
  **client-side**. Hasil dilaporkan ke server untuk penyimpanan.
  Acceptable untuk MVP karena XP tidak bernilai riil. Fase berikutnya
  perlu server-side verification (Piston/Judge0).

### 7.4 XSS Mitigation
- Render markdown via `marked` + sanitasi dasar (strip `<script>` &
  event handler attributes).
- Fase berikutnya: integrasi DOMPurify untuk sanitasi yang lebih kuat.

### 7.5 CSRF & SQL Injection
- Laravel default CSRF token pada form.
- Eloquent ORM + parameter binding → tidak ada raw SQL query.

---

## 8. Frontend State Management

MVP tidak pakai state management library (Pinia/Vuex). State dikelola
via:

1. **Inertia shared props** — `auth.user`, `sidebarOpen` di-share dari
   server via `HandleInertiaRequests`.
2. **Local component state** — `ref()` / `reactive()` per komponen.
3. **Composable singleton** — `usePyodide` pakai module-level state
   (`pyodideReady`, dll) untuk reuse lintas komponen.
4. **Flash messages** — server kirim via `back()->with()`, frontend
   baca via `page.props.flash`.

---

## 9. Testing Strategy

### 9.1 Backend Tests (Pest 5)

| Test File                          | Coverage                              |
|------------------------------------|---------------------------------------|
| `AdminGateTest`                    | Gate admin terdaftar, admin/non-admin |
| `BlockAttemptTest`                 | Submit semua tipe block + non-graded reject |
| `LessonAutoCompletionTest`         | Auto-complete logic (single, multi, idempotent) |
| `AdminAuthorizationTest`           | 403 non-admin, 200 admin, CRUD + reorder |
| `PlaygroundTest`                   | Guest redirect, auth access, starter code |
| `DashboardTest` + Auth tests       | Default starter kit tests             |

Total: 74 tests passing, 4 skipped, 0 failed.

### 9.2 Frontend Tests
- MVP: tidak ada unit test frontend (Vue components).
- Fase berikutnya: Vitest untuk composable (`usePyodide`) & component
  testing untuk block components.

### 9.3 Quality Check Commands

```bash
# PHP
composer lint:check     # Pint
composer types:check    # PHPStan
php artisan test        # Pest

# Frontend
npm run lint:check      # ESLint
npm run format:check    # Prettier
npm run types:check     # vue-tsc
npm run build           # Vite build

# CI lengkap
composer ci:check
```

---

## 10. Deployment Architecture

### 10.1 MVP Deployment

```
┌────────────────────────────────┐
│       Single Server (VPS)      │
│                                │
│  ┌──────────────────────────┐  │
│  │  Nginx / Apache          │  │
│  │  (reverse proxy)         │  │
│  └────────────┬─────────────┘  │
│               │                │
│  ┌────────────▼─────────────┐  │
│  │  PHP-FPM 8.3             │  │
│  │  (Laravel app)           │  │
│  └────────────┬─────────────┘  │
│               │                │
│  ┌────────────▼─────────────┐  │
│  │  SQLite (file)           │  │
│  │  database/database.sqlite│  │
│  └──────────────────────────┘  │
│                                │
│  Queue worker (database driver)│
└────────────────────────────────┘
```

### 10.2 Build & Deploy Steps

```bash
composer setup        # install + migrate + build assets
composer dev          # development (server + queue + vite concurrent)

# Production
php artisan config:cache
php artisan route:cache
npm run build         # Vite build → public/build/
```

### 10.3 Scalability Path

| Saat          | Aksi                                          |
|---------------|-----------------------------------------------|
| User > 1K     | Migrasi DB SQLite → PostgreSQL                |
| Traffic tinggi| Tambah load balancer + multiple app server    |
| Queue penuh   | Redis untuk queue/cache broker                |
| Multi-bahasa  | Tambah server-side code execution (Piston)    |

---

## 11. Key Design Decisions

### 11.1 Pyodide vs Server-side Execution
**Pilihan**: Pyodide (WASM di browser).
**Alasan**: Aman (sandbox browser), murah (no server compute), cepat
(cached setelah load pertama). Trade-off: hanya Python, butuh internet
saat load pertama.

### 11.2 CodeMirror vs Monaco
**Pilihan**: CodeMirror 6.
**Alasan**: Bundle ringan (~150KB vs ~2MB Monaco), modular,
mobile-friendly, cukup untuk Python editing.

### 11.3 Client-side vs Server-side Verification
**Pilihan**: Hybrid — MCQ/Fill/Reorder server-side, Code Challenge
client-side.
**Alasan**: Server tidak punya Python runtime di MVP. Client-side
acceptable karena XP tidak bernilai riil. Fase berikutnya pindah ke
server-side (Piston API).

### 11.4 Shallow Nested Routes untuk Admin
**Pilihan**: `Route::resource(...)->shallow()`.
**Alasan**: URL index/create nested (konteks parent), tapi show/edit/
update/destroy flat untuk URL yang lebih pendek dan binding sederhana.

### 11.5 Singleton Pyodide via Module-level State
**Pilihan**: Module-level state di `usePyodide.ts` (bukan Pinia/Vuex).
**Alasan**: Pyodide instance harus reuse (load sekali ~10MB). Module-
level singleton sederhana dan cukup tanpa state management library.

### 11.6 SQLite untuk MVP
**Pilihan**: SQLite (default Laravel).
**Alasan**: Zero-config, cukup untuk single-server MVP. Saat skala,
migrasi ke PostgreSQL mudah (Eloquent ORM agnostik).

---

## 12. Glossary

| Istilah         | Definisi                                           |
|-----------------|----------------------------------------------------|
| Block           | Unit konten terkecil dalam lesson (1 dari 8 tipe)  |
| Graded Block    | Block yang menyimpan attempt (MCQ, Code, dll)      |
| Lesson          | Kumpulan block yang diajarkan berurutan            |
| Module          | Kumpulan lesson dalam course                       |
| Course          | Kumpulan module (mis. "Python Fundamentals")       |
| Pyodide         | Python runtime dikompilasi ke WASM, jalan di browser |
| Inertia.js      | Library untuk SPA-like UX tanpa API terpisah       |
| Wayfinder       | Laravel package untuk generate typed route helpers |
| Graded          | Block yang jawabannya dinilai (benar/salah)        |
| Attempt         | Record jawaban siswa untuk suatu graded block      |
