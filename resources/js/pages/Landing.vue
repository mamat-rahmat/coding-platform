<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle2,
    GraduationCap,
    Play,
    Rocket,
    Terminal,
    Trophy,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { login, register } from '@/routes';
import courseRoutes from '@/routes/courses';

const isAuthenticated = computed(() => Boolean(usePage().props.auth?.user));

const features = [
    {
        icon: Terminal,
        title: 'Jalankan Kode di Browser',
        description:
            'Setiap contoh kode bisa langsung dijalankan tanpa install apa pun. Eksekusi Python berjalan di browser-mu via Pyodide (WASM).',
    },
    {
        icon: CheckCircle2,
        title: 'Tantangan Interaktif',
        description:
            'Isi bagian yang kosong, urutkan kode, dan pecahkan tantangan pemrograman dengan umpan balik langsung.',
    },
    {
        icon: BookOpen,
        title: 'Belajar Bertahap',
        description:
            'Materi tersusun rapi dalam modul dan pelajaran yang harus diselesaikan berurutan, dari nol sampai mahir.',
    },
    {
        icon: Trophy,
        title: 'Papan Peringkat',
        description:
            'Bandingkan progresmu dengan peserta lain di setiap course dan kejar posisi teratas.',
    },
];

const steps = [
    {
        number: '01',
        title: 'Pilih Course',
        description: 'Pilih course Python yang sesuai levelmu.',
    },
    {
        number: '02',
        title: 'Pelajari Materi',
        description:
            'Ikuti pelajaran dengan contoh kode yang bisa langsung dicoba.',
    },
    {
        number: '03',
        title: 'Selesaikan Tantangan',
        description: 'Kerjakan tantangan di akhir materi untuk naik level.',
    },
    {
        number: '04',
        title: 'Naik Peringkat',
        description: 'Pantau posisimu di leaderboard setiap course.',
    },
];
</script>

<template>
    <div class="min-h-screen bg-white text-gray-900">
        <header class="border-b border-gray-100">
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6"
            >
                <Link href="/" class="flex items-center gap-2.5">
                    <div
                        class="flex aspect-square size-9 items-center justify-center rounded-lg bg-gray-900 text-white"
                    >
                        <AppLogoIcon class="size-5 fill-current" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight">
                        CodePioneer
                    </span>
                </Link>

                <nav class="flex items-center gap-6 text-sm font-medium">
                    <Link
                        :href="courseRoutes.index.url()"
                        class="text-gray-600 hover:text-gray-900"
                    >
                        Courses
                    </Link>

                    <template v-if="isAuthenticated">
                        <Link
                            :href="courseRoutes.index.url()"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-white transition hover:bg-gray-700"
                        >
                            Mulai Belajar
                        </Link>
                    </template>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="text-gray-600 hover:text-gray-900"
                        >
                            Masuk
                        </Link>
                        <Link
                            :href="register()"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-white transition hover:bg-gray-700"
                        >
                            Daftar Gratis
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main>
            <section class="mx-auto max-w-7xl px-6 pt-20 pb-16 text-center">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-600"
                >
                    Belajar Python · Interaktif · Tanpa Setup
                </span>

                <h1
                    class="mx-auto mt-6 max-w-3xl text-5xl font-bold tracking-tight text-balance"
                >
                    Belajar Python dengan
                    <span class="text-gray-400">Menulis & Menjalankan</span>
                    Kode Nyata
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600">
                    Pelajari dasar-dasar pemrograman lewat pelajaran singkat,
                    contoh kode yang bisa langsung dijalankan, dan tantangan
                    interaktif — semuanya di browser.
                </p>

                <div
                    class="mt-8 flex flex-wrap items-center justify-center gap-3"
                >
                    <Link
                        :href="courseRoutes.index.url()"
                        class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-6 py-3 text-base font-medium text-white transition hover:bg-gray-700"
                    >
                        <Play class="h-5 w-5" />
                        Mulai Belajar Sekarang
                    </Link>

                    <Link
                        :href="courseRoutes.index.url()"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 text-base font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        <Trophy class="h-5 w-5" />
                        Lihat Peringkat
                    </Link>
                </div>

                <div
                    class="mx-auto mt-16 max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-gray-900 text-left shadow-xl"
                >
                    <div
                        class="flex items-center gap-2 border-b border-gray-800 px-4 py-3"
                    >
                        <span class="h-3 w-3 rounded-full bg-red-500/80"></span>
                        <span
                            class="h-3 w-3 rounded-full bg-yellow-500/80"
                        ></span>
                        <span
                            class="h-3 w-3 rounded-full bg-green-500/80"
                        ></span>
                        <span class="ml-3 text-xs text-gray-400">
                            main.py
                        </span>
                    </div>

                    <pre
                        class="overflow-x-auto p-6 font-mono text-sm leading-7 text-gray-100"
                    ><code><span class="text-fuchsia-400">print</span>(<span class="text-green-400">"Hello, World!"</span>)
<span class="text-blue-400">for</span> i <span class="text-blue-400">in</span> <span class="text-yellow-300">range</span>(<span class="text-orange-300">1</span>, <span class="text-orange-300">4</span>):
    <span class="text-fuchsia-400">print</span>(f<span class="text-green-400">"Halo dari Python #{i}"</span>)

<span class="text-gray-500"># Output:</span>
<span class="text-gray-300">Hello, World!</span>
<span class="text-gray-300">Halo dari Python #1</span>
<span class="text-gray-300">Halo dari Python #2</span>
<span class="text-gray-300">Halo dari Python #3</span></code></pre>
                </div>
            </section>

            <section class="border-t border-gray-100 bg-gray-50 py-20">
                <div class="mx-auto max-w-7xl px-6">
                    <h2 class="text-center text-3xl font-bold tracking-tight">
                        Semua yang kamu butuhkan untuk mulai coding
                    </h2>

                    <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div
                            v-for="feature in features"
                            :key="feature.title"
                            class="rounded-2xl border border-gray-200 bg-white p-6"
                        >
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 text-gray-900"
                            >
                                <component :is="feature.icon" class="h-5 w-5" />
                            </div>

                            <h3 class="mt-4 font-semibold">
                                {{ feature.title }}
                            </h3>

                            <p
                                class="mt-2 text-sm leading-relaxed text-gray-600"
                            >
                                {{ feature.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-6 py-20">
                <h2 class="text-center text-3xl font-bold tracking-tight">
                    Cara kerjanya
                </h2>

                <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="step in steps"
                        :key="step.number"
                        class="relative rounded-2xl border border-gray-200 p-6"
                    >
                        <span class="text-4xl font-bold text-gray-200">{{
                            step.number
                        }}</span>
                        <h3 class="mt-3 font-semibold">{{ step.title }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ step.description }}
                        </p>
                    </div>
                </div>

                <div class="mt-16 text-center">
                    <p class="text-lg text-gray-600">
                        Siap memulai perjalanan codemu?
                    </p>

                    <Link
                        :href="courseRoutes.index.url()"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gray-900 px-8 py-3.5 text-base font-medium text-white transition hover:bg-gray-700"
                    >
                        <Rocket class="h-5 w-5" />
                        Mulai Belajar Sekarang
                    </Link>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-100 py-10">
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 text-sm text-gray-500 sm:flex-row"
            >
                <div class="flex items-center gap-2">
                    <GraduationCap class="h-4 w-4" />
                    <span>CodePioneer — Belajar Python interaktif</span>
                </div>

                <div class="flex items-center gap-6">
                    <Link
                        :href="courseRoutes.index.url()"
                        class="hover:text-gray-900"
                    >
                        Courses
                    </Link>
                    <Link :href="login()" class="hover:text-gray-900">
                        Masuk
                    </Link>
                </div>
            </div>
        </footer>
    </div>
</template>
