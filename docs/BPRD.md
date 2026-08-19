# Business & Product Requirements Document (BPRD)
## coding-platform — Platform Belajar Pemrograman Interaktif

---

## 1. Document Information

### 1.1 Document Purpose
Dokumen ini mendefinisikan kebutuhan bisnis dan produk untuk platform
belajar pemrograman interaktif **coding-platform** (ala SoloLearn). Dokumen
menjadi acuan tunggal bagi tim pengembang, stakeholder, dan kontributor
untuk memahami scope, fitur, dan kriteria penerimaan MVP.

### 1.2 Version History

| Versi | Tanggal       | Deskripsi                          | Penulis      |
|-------|---------------|------------------------------------|--------------|
| 1.0   | 19 Agt 2026   | Dokumen awal untuk MVP Python-only | Tim Produk   |

### 1.3 Document Owner
Tim Produk coding-platform.

### 1.4 Stakeholders
- **Product Owner**: pemilik visi produk dan prioritas fitur
- **Developer**: implementasi teknis (backend Laravel + frontend Vue)
- **Admin Konten**: pembuat dan pengelola materi course
- **Siswa (End User)**: pengguna yang belajar pemrograman

---

## 2. Background

### 2.1 Business Context
Pemrograman adalah skill esensial di era digital, namun hambatan utama
bagi pemula adalah kurangnya platform berbahasa Indonesia yang
interaktif — tidak hanya membaca, tapi juga menulis dan mengeksekusi
kode langsung. SoloLearn sudah membuktikan model "bite-sized lesson +
interaktif" berhasil secara global, namun belum ada pemain lokal yang
mengisi ruang ini dengan serius.

### 2.2 Problem Statement
- Materi pemrograman berbahasa Indonesia mayoritas berupa artikel
  pasif (blog/video) tanpa elemen praktik langsung.
- Platform berbahasa Inggris seperti SoloLearn menjadi rujukan, tapi
  siswa pemula kesulitan karena hambatan bahasa.
- Tidak ada platform lokal yang menggabungkan materi bite-sized, kuis
  interaktif, latihan coding, dan playground dalam satu ekosistem.

### 2.3 Current Condition
- Belum ada produk yang berjalan.
- Repo `coding-platform` berisi skeleton Laravel + Vue starter kit
  dengan satu course demo Python yang terseed otomatis.

### 2.4 Existing Process / System
Tidak ada sistem existing. MVP akan dibangun dari skeleton yang sudah
ada di repo (Laravel 13 + Vue 3 + Inertia.js + SQLite).

---

## 3. Objectives & Goals

### 3.3 Business Objectives
- Menyediakan platform belajar pemrograman berbahasa Indonesia yang
  interaktif dan mudah diakses.
- Validasi product-market fit untuk segmen pemula Python dalam 3 bulan
  setelah rilis MVP.
- Menjadi rujukan utama belajar coding interaktif berbahasa Indonesia.

### 3.2 Product Goals
- MVP fokus **Python saja** dengan 8 tipe block interaktif.
- Eksekusi kode berjalan **di browser** via Pyodide (tanpa server
  execution sandbox) — aman dan murah.
- Admin panel lengkap untuk authoring konten tanpa perlu coding manual
  di seeder.
- UX ala SoloLearn: bite-sized, progres terlihat, auto-complete lesson.

### 3.3 Success Metrics / KPIs

| KPI                              | Target MVP (3 bulan) |
|----------------------------------|----------------------|
| Jumlah siswa terdaftar           | 500                  |
| Course completion rate           | ≥ 30%                |
| Daily Active Users (DAU)         | 50                   |
| Jumlah lesson diselesaikan/hari | 100                  |
| NPS                              | ≥ 40                 |

---

## 4. Stakeholders & Users

### 4.1 Stakeholders
Lihat section 1.4.

### 4.2 User Types

| Tipe       | Deskripsi                                                   |
|------------|-------------------------------------------------------------|
| Siswa      | Belajar course, jalankan kode di playground                 |
| Admin      | Kelola course/module/lesson/block via admin panel           |
| Guest      | Bisa lihat halaman welcome, tapi harus login untuk akses    |

### 4.3 Personas

**Persona 1 — Andi, pemula Python (Siswa)**
- _Usia 17 tahun, pelajar SMA._
- Ingin belajar Python dari nol tapi bingung mulai dari mana.
- Butuh materi pendek, praktik langsung, dan umpan balik instan.
- _Quote_: "Saya bosan baca artikel panjang. Saya ingin ketik kode dan
  langsung lihat hasilnya."

**Persona 2 — Pak Budi, pengajar (Admin Konten)**
- _Usia 35 tahun, guru informatika._
- Ingin membuat materi Python bite-sized untuk siswanya.
- Tidak ingin coding manual — butuh form admin yang intuitif.
- _Quote_: "Saya ingin buat lesson baru, tambah kuis dan latihan coding
  tanpa minta bantuan developer."

---

## 5. Scope

### 5.1 In Scope (MVP)
- Autentikasi (register, login, reset password) via Laravel Fortify.
- Course catalog untuk siswa.
- Lesson viewer dengan 8 tipe block:
  `TEXT`, `CODE_EXAMPLE`, `HINT`, `MCQ_SINGLE`, `MCQ_MULTIPLE`,
  `CODE_FILL`, `CODE_REORDER`, `CODE_CHALLENGE`.
- Auto-completion lesson berdasarkan jawaban benar.
- Playground Python dengan eksekusi Pyodide di browser.
- Admin panel: CRUD Course → Module → Lesson → LessonBlock.
- 8 per-type block editor di admin.
- Block reordering via drag-and-drop.
- Preview lesson sebagai siswa.
- Role admin via `is_admin` + Gate + middleware.
- Bahasa konten: Bahasa Indonesia.
- Platform: desktop-first (responsif dasar).

### 5.2 Out of Scope (MVP)
- Dukungan bahasa selain Python (C/C++, JavaScript, dll).
- Server-side code execution sandbox (Docker/nsjail).
- Sistem XP, leveling, badge, dan streak harian.
- Leaderboard global/harian.
- Diskusi/Q&A per lesson.
- Sertifikat penyelesaian course.
- Mobile app native (PWA/mobile-first UX).
- Notifikasi email/push.
- Multi-tenancy / organisasi.
- Integrasi payment / monetisasi.

### 5.3 Assumptions
- Siswa memiliki koneksi internet saat runtime (Pyodide fetch ~10MB
  WASM binary dari CDN saat pertama kali; di-cache browser setelahnya).
- Browser modern yang mendukung WebAssembly.
- Admin adalah pihak terpercaya — verifikasi jawaban
  `CODE_CHALLENGE`/`CODE_FILL` bersifat client-side (hasil dilaporkan ke
  server tanpa verifikasi independen). Acceptable untuk MVP karena XP
  tidak bernilai riil.

### 5.4 Constraints
- DB: SQLite (default), sesi/antrian/cache pakai database.
- Code editor: CodeMirror 6 (bukan Monaco) untuk menjaga bundle ringan.
- Pyodide dimuat dari CDN jsdelivr dengan version pin (tidak di-bundle
  via npm) untuk menjaga ukuran bundle Vite tetap kecil.
- Bahasa konten: Bahasa Indonesia (tidak multi-bahasa di MVP).

---

## 6. Product Overview

### 6.1 Product Vision
"Platform belajar pemrograman berbahasa Indonesia tempat siapa pun bisa
menulis dan mengeksekusi kode sejak menit pertama — bite-sized,
interaktif, dan menyenangkan."

### 6.2 Product Overview
Web app di mana siswa memilih course, menyelesaikan lesson yang terdiri
dari blok-blok pendek (materi teks, contoh kode, kuis, latihan coding),
dan akhirnya dapat berlatih bebas di playground. Admin mengelola seluruh
konten melalui panel admin terpisah.

### 6.3 Main Features

| ID  | Fitur                        | Tipe User |
|-----|------------------------------|-----------|
| F1  | Autentikasi                  | Siswa     |
| F2  | Course catalog & progress    | Siswa     |
| F3  | Lesson viewer (8 block type) | Siswa     |
| F4  | Auto-completion lesson       | Siswa     |
| F5  | Playground Python            | Siswa     |
| F6  | Admin dashboard & stats      | Admin     |
| F7  | CRUD Course/Module/Lesson    | Admin     |
| F8  | Per-type block editor        | Admin     |
| F9  | Block reordering             | Admin     |
| F10 | Preview as student           | Admin     |

### 6.4 User Journey

**Siswa (Andi):**
1. Daftar akun → login.
2. Buka **Courses** dari sidebar.
3. Pilih course "Python Fundamentals".
4. Lihat progress bar course (0%).
5. Buka lesson pertama "Hello Python".
6. Baca materi TEXT → lihat CODE_EXAMPLE → buka HINT.
7. Jawab MCQ_SINGLE → umpan balik instan (benar/salah).
8. Lanjut ke CODE_CHALLENGE → tulis kode → tekan **Run Testcases**.
9. Semua graded block terjawab benar → lesson auto-complete.
10. Progress bar course naik → lanjut lesson berikutnya.
11. Selesai course → buka **Playground** untuk eksplorasi bebas.

**Admin (Pak Budi):**
1. Login sebagai admin → sidebar menampilkan menu **Admin**.
2. Buka Admin Dashboard → lihat statistik.
3. Klik **Courses** → **Course Baru** → isi form → simpan.
4. Buka course → tambah **Module** → tambah **Lesson**.
5. Buka lesson → klik **Block Baru** → pilih tipe (mis. MCQ_SINGLE).
6. Lengkapi konten via per-type editor → simpan.
7. Reorder block via drag-and-drop → **Save Order**.
8. Klik **Preview as Student** untuk verifikasi tampilan.
9. Set `is_published = true` saat siap dipublikasikan.

---

## 7. Functional Requirements

### 7.1 F1 — Autentikasi

#### 7.1.1 Description
Siswa dapat mendaftar, login, reset password, dan mengelola profil.
Disediakan oleh Laravel Fortify (out-of-the-box starter kit).

#### 7.1.2 User Stories
- Sebagai guest, saya ingin mendaftar agar bisa mengakses course.
- Sebagai siswa, saya ingin login agar progres saya tersimpan.
- Sebagai siswa, saya ingin reset password jika lupa.

#### 7.1.3 Business Rules
- Email unik per akun.
- Password memenuhi kebijakan default Laravel Fortify.
- Rate limiting login: 5 percobaan per menit per IP+email.

#### 7.1.4 User Flow
Welcome → Login/Register → sukses → Dashboard.

#### 7.1.5 Acceptance Criteria
- Guest yang klik course/playground diarahkan ke halaman login.
- Setelah login, redirect ke dashboard.
- Email verification tersedia (opsional di MVP).

---

### 7.2 F2 — Course Catalog & Progress

#### 7.2.1 Description
Siswa melihat daftar course published dan progress penyelesaian per
course.

#### 7.2.2 User Stories
- Sebagai siswa, saya ingin melihat daftar course yang tersedia.
- Sebagai siswa, saya ingin melihat progress saya per course (persen).

#### 7.2.3 Business Rules
- Hanya course dengan `is_published = true` yang ditampilkan.
- Progress = (lesson diselesaikan / total lesson published) × 100%.

#### 7.2.4 User Flow
Courses → pilih course → halaman detail course dengan modul & lesson.

#### 7.2.5 Acceptance Criteria
- Course draft tidak muncul di catalog siswa.
- Progress bar ditampilkan dengan persentase integer.

---

### 7.3 F3 — Lesson Viewer (8 Tipe Block)

#### 7.3.1 Description
Lesson terdiri dari urutan block. Setiap block punya tipe yang
menentukan cara render dan interaksi.

#### 7.3.2 User Stories
- Sebagai siswa, saya ingin membaca materi teks berformat markdown.
- Sebagai siswa, saya ingin melihat contoh kode dengan syntax
  highlighting.
- Sebagai siswa, saya ingin membuka petunjuk (hint) secara opsional.
- Sebagai siswa, saya ingin menjawab kuis pilihan ganda (single &
  multiple answer).
- Sebagai siswa, saya ingin melengkapi kode yang kosong.
- Sebagai siswa, saya ingin menyusun ulang baris kode.
- Sebagai siswa, saya ingin menulis & mengeksekusi kode Python dengan
  testcase.

#### 7.3.3 Business Rules
- Hanya lesson dengan `is_published = true` yang bisa diakses siswa.
- Block graded: `MCQ_SINGLE`, `MCQ_MULTIPLE`, `CODE_FILL`,
  `CODE_REORDER`, `CODE_CHALLENGE`.
- Block non-graded: `TEXT`, `CODE_EXAMPLE`, `HINT`.
- Verifikasi jawaban:
  - `MCQ_SINGLE`, `MCQ_MULTIPLE`, `CODE_REORDER`: server-side compare.
  - `CODE_FILL`, `CODE_CHALLENGE`: client-side (hasil dilaporkan ke
    server untuk disimpan).

#### 7.3.4 User Flow
Buka lesson → render block per tipe → interaksi per block → submit
jawaban → umpan balik instan.

#### 7.3.5 Acceptance Criteria
- Setiap tipe block punya komponen Vue terpisah.
- `LessonBlockRenderer` dispatch ke komponen yang benar berdasarkan
  `block.type`.
- Tipe block tidak dikenal menampilkan "Block type belum didukung".
- Submit jawaban menyimpan `BlockAttempt` dengan `is_correct`.

---

### 7.4 F4 — Auto-Completion Lesson

#### 7.4.1 Description
Lesson otomatis termark selesai ketika semua graded block dalam lesson
tersebut memiliki ≥1 jawaban benar dari user.

#### 7.4.2 User Stories
- Sebagai siswa, saya ingin lesson otomatis termark selesai tanpa harus
  klik tombol manual.
- Sebagai siswa, saya ingin tombol "Tandai selesai" tetap tersedia untuk
  lesson tanpa block graded.

#### 7.4.3 Business Rules
- Auto-complete hanya untuk lesson dengan ≥1 graded block.
- Lesson tanpa graded block mengandalkan tombol manual.
- Operasi idempotent (via `updateOrCreate`).

#### 7.4.4 User Flow
Submit jawaban benar → server cek semua graded block punya correct
attempt → jika ya, create/update `LessonProgress`.

#### 7.4.5 Acceptance Criteria
- Lesson dengan 2 graded block baru complete ketika keduanya terjawab
  benar.
- Lesson tanpa graded block tidak pernah auto-complete.
- `LessonProgress` tidak duplikat saat auto-complete dipanggil ulang.

---

### 7.5 F5 — Playground Python

#### 7.5.1 Description
Editor Python bebas di mana siswa menulis kode dan mengeksekusinya
langsung di browser via Pyodide.

#### 7.5.2 User Stories
- Sebagai siswa, saya ingin menulis kode Python bebas dan menjalankannya.
- Sebagai siswa, saya ingin memberikan stdin untuk program yang butuh
  input.

#### 7.5.3 Business Rules
- Harus login untuk akses playground.
- Eksekusi 100% client-side (browser), server tidak menjalankan kode.
- Starter code disediakan dari server, siswa bebas mengubah.

#### 7.5.4 User Flow
Playground → tulis kode → (opsional) isi stdin → Run → output muncul.

#### 7.5.5 Acceptance Criteria
- Tombol Run mengeksekusi kode dan menampilkan stdout + stderr.
- Pyodide dimuat lazy (hanya saat pertama kali Run diklik).
- Loading state ditampilkan saat Pyodide sedang dimuat.

---

### 7.6 F6 — Admin Dashboard & Stats

#### 7.6.1 Description
Dashboard admin menampilkan ringkasan statistik platform.

#### 7.6.2 User Stories
- Sebagai admin, saya ingin melihat jumlah course/lesson/block/user/
  attempt.

#### 7.6.3 Business Rules
- Hanya user dengan `is_admin = true` yang bisa akses.
- Stats realtime (query DB setiap kunjungan).

#### 7.6.5 Acceptance Criteria
- Non-admin yang akses `/admin` mendapat HTTP 403.
- Guest diarahkan ke login.
- Stats menampilkan: courses, published courses, lessons, blocks (per
  tipe), users, admins, attempts, correct attempts.

---

### 7.7 F7 — CRUD Course/Module/Lesson

#### 7.7.1 Description
Admin dapat membuat, membaca, mengupdate, dan menghapus course, module,
dan lesson.

#### 7.7.2 User Stories
- Sebagai admin, saya ingin membuat course baru.
- Sebagai admin, saya ingin menambah module ke course.
- Sebagai admin, saya ingin menambah lesson ke module.

#### 7.7.3 Business Rules
- Hierarki: Course → Module → Lesson (nested, shallow routes).
- Hapus course cascade-delete module terkait.
- Validasi: title, slug, dan field wajib via FormRequest.

#### 7.7.5 Acceptance Criteria
- Non-admin mendapat 403 pada semua operasi CRUD.
- Slug unik per entitas.
- Setelah create, redirect ke halaman detail entitas.

---

### 7.8 F8 — Per-Type Block Editor

#### 7.8.1 Description
Admin dapat membuat dan mengedit block dengan form khusus sesuai tipe
block yang dipilih.

#### 7.8.2 User Stories
- Sebagai admin, saya ingin memilih tipe block saat membuat block baru.
- Sebagai admin, saya ingin form editor yang berbeda per tipe block.

#### 7.8.3 Business Rules
- 8 editor terpisah sesuai `LessonBlockType`.
- `BlockEditorDispatcher` memilih editor berdasarkan `type`.
- Saat create, default content disiapkan per tipe.

#### 7.8.5 Acceptance Criteria
- Setiap tipe block punya editor yang sesuai dengan schema content.
- Editor menyimpan perubahan via `v-model` ke parent.
- Submit form menyimpan `type`, `content` (JSON), `sort_order`.

---

### 7.9 F9 — Block Reordering

#### 7.9.1 Description
Admin dapat menyusun ulang urutan block dalam lesson via drag-and-drop.

#### 7.9.2 User Stories
- Sebagai admin, saya ingin menyusun urutan block dengan mudah.

#### 7.9.3 Business Rules
- Reorder hanya mempengaruhi block milik lesson tersebut.
- Endpoint `PATCH /admin/lessons/{lesson}/blocks/reorder` dengan array
  `[{id, sort_order}]`.
- Transaksi DB untuk menjaga konsistensi.

#### 7.9.5 Acceptance Criteria
- Drag-and-drop menggunakan vuedraggable.
- Tombol "Save Order" muncul saat ada perubahan urutan.
- Reorder block dari lesson lain tidak berdampak.

---

### 7.10 F10 — Preview as Student

#### 7.10.1 Description
Admin dapat melihat tampilan lesson seperti siswa lihat, tanpa harus
logout.

#### 7.10.2 User Stories
- Sebagai admin, saya ingin memverifikasi tampilan lesson sebelum
  dipublikasikan.

#### 7.10.5 Acceptance Criteria
- Tombol "Preview as Student" di halaman admin lesson/detail course.
- Link mengarah ke route siswa `lessons.show` (pakai slug).

---

## 8. Non-Functional Requirements

### 8.1 Performance
- First Contentful Paint < 2 detik pada koneksi broadband.
- Pyodide load awal ~10MB dari CDN; cache browser untuk kunjungan
  berikutnya.
- Page transition via Inertia.js (SPA-like, < 500ms).

### 8.2 Availability
- MVP di-host di satu instance (tidak HA).
- Target uptime: 99% (maintenance window di luar jam sibuk).

### 8.3 Security
- Auth via Laravel Fortify (BCrypt, rate limiting login).
- Role admin via `is_admin` + Gate + middleware `EnsureUserIsAdmin`.
- Policy enforcement pada setiap operasi CRUD admin.
- Sanitasi dasar pada render markdown (strip `<script>` & event handler
  attributes).
- ⚠️ Verifikasi `CODE_CHALLENGE` client-side — acceptable untuk MVP
  karena XP tidak bernilai riil. Fase berikutnya perlu server-side
  verification.

### 8.4 Accessibility
- Target MVP: WCAG 2.1 Level A (best effort).
- Kontras warna memadai, navigasi keyboard dasar.
- Code editor CodeMirror mendukung keyboard navigation.

### 8.5 Scalability
- DB SQLite cukup untuk MVP (single-writer).
- Pyodide berjalan di browser — tidak beban server.
- Saat skala, pindah DB ke PostgreSQL/MySQL dan tambah queue worker.

---

## 9. Integrations

### 9.1 External Systems
- **CDN jsdelivr**: host Pyodide WASM binary dengan version pin
  (`PYODIDE_VERSION` di `usePyodide.ts`).
- **Bunny Fonts**: font "Instrument Sans" (via `laravel-vite-plugin`).

### 9.2 Data Exchange
- Tidak ada integrasi data dengan sistem eksternal di MVP.

### 9.3 Integration Requirements
- Pyodide harus dimuat lazy dan dengan version pin untuk reproducibility.
- CDN fallback tidak diimplementasikan di MVP (single-CDN).

---

## 10. Reporting & Analytics

### 10.1 Required Reports
- **Admin Dashboard**: statistik agregat (lihat F6).

### 10.2 Metrics

| Metric                    | Sumber       |
|---------------------------|--------------|
| Jumlah user terdaftar     | `users`      |
| Jumlah course published   | `courses`    |
| Jumlah lesson published   | `lessons`    |
| Jumlah block per tipe     | `lesson_blocks` |
| Jumlah attempt            | `block_attempts` |
| Jumlah attempt benar      | `block_attempts.is_correct` |

### 10.3 Analytics Events
- MVP: tidak ada event tracking terpisah (mis. PostHog/Mixpanel).
- Fase berikutnya: track lesson_started, lesson_completed,
  block_answered, playground_run.

---

## 11. Risks & Dependencies

| Risk                                                  | Impact | Mitigasi                                  |
|-------------------------------------------------------|--------|-------------------------------------------|
| Verifikasi client-side bisa dimanipulasi siswa        | Sedang | Acceptable MVP; rencana server-side verification fase berikutnya |
| Pyodide membutuhkan koneksi internet saat runtime     | Rendah | Cache browser setelah load pertama; dokumentasikan requirement |
| Bundle Vite membesar saat banyak editor ditambah      | Rendah | Code-split via dynamic import jika perlu  |
| SQLite single-writer menjadi bottleneck               | Rendah | Migrasi ke PostgreSQL saat skala          |
| Konten markdown berbahaya (XSS)                       | Sedang | Sanitasi dasar; fase berikutnya pakai DOMPurify |

**Dependencies:**
- Pyodide (CDN jsdelivr) untuk eksekusi Python.
- CodeMirror 6 (`vue-codemirror`, `@codemirror/lang-python`).
- Laravel Fortify untuk auth.
- Laravel Wayfinder untuk typed route helpers.

---

## 12. Release / MVP Scope

MVP mencakup seluruh fitur F1–F10 dengan batasan:
- Python-only.
- Eksekusi kode via Pyodide (browser, bukan server sandbox).
- Bahasa konten: Bahasa Indonesia.
- Desktop-first.
- Tidak ada XP/leaderboard/diskusi/sertifikat.

**Fase berikutnya (post-MVP):**
- Server-side code verification (Piston/Judge0).
- Dukungan C/C++ dan JavaScript.
- Sistem XP, leveling, badge, streak harian.
- Leaderboard.
- Diskusi/Q&A per lesson.
- Sertifikat penyelesaian.
- Mobile-first UX / PWA.

---

## 13. Acceptance & Sign-off

Dokumen ini diterima oleh:

| Role             | Nama           | Tanggal       | Tanda Tangan |
|------------------|----------------|---------------|--------------|
| Product Owner    |                |               |              |
| Tech Lead        |                |               |              |
| Admin Konten     |                |               |              |

Dengan menandatangani, stakeholder setuju bahwa MVP akan dikembangkan
sesuai scope di dokumen ini. Perubahan signifikan harus melalui version
update pada section 1.2.
