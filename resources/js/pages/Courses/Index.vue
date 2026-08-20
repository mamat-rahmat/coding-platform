<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Trophy } from '@lucide/vue';
import { computed } from 'vue';
import courseRoutes from '@/routes/courses';

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    language: string;
    level: string;
    thumbnail: string | null;
    xp_reward: number;
    modules_count: number;
}

defineProps<{
    courses: Course[];
}>();

const isAuthenticated = computed(() => Boolean(usePage().props.auth?.user));
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-12">
            <div class="mb-10">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    Learn to Code
                </h1>

                <p class="mt-2 text-gray-600">
                    Pilih course dan mulai belajar pemrograman.
                </p>
            </div>

            <div
                v-if="courses.length === 0"
                class="rounded-xl border border-dashed bg-white p-12 text-center"
            >
                <p class="text-gray-500">Belum ada course yang tersedia.</p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="course in courses"
                    :key="course.id"
                    class="overflow-hidden rounded-xl border bg-white shadow-sm transition hover:shadow-md"
                >
                    <div class="aspect-video bg-gray-100">
                        <img
                            v-if="course.thumbnail"
                            :src="course.thumbnail"
                            :alt="course.title"
                            class="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="flex h-full items-center justify-center text-4xl font-bold text-gray-300"
                        >
                            {{ course.language.toUpperCase() }}
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="mb-3 flex items-center gap-2">
                            <span
                                class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700"
                            >
                                {{ course.level }}
                            </span>

                            <span
                                class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700"
                            >
                                {{ course.language }}
                            </span>
                        </div>

                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ course.title }}
                        </h2>

                        <p class="mt-2 line-clamp-2 text-sm text-gray-600">
                            {{ course.description }}
                        </p>

                        <div
                            class="mt-4 flex items-center justify-between text-sm text-gray-500"
                        >
                            <span> {{ course.modules_count }} modules </span>

                            <span> {{ course.xp_reward }} XP </span>
                        </div>

                        <div class="mt-5 flex flex-col gap-2">
                            <Link
                                v-if="isAuthenticated"
                                :href="courseRoutes.show.url(course.slug)"
                                class="block w-full rounded-lg bg-gray-900 px-4 py-2.5 text-center text-sm font-medium text-white transition hover:bg-gray-700"
                            >
                                Mulai Belajar
                            </Link>

                            <Link
                                :href="
                                    courseRoutes.leaderboard.url(course.slug)
                                "
                                class="flex w-full items-center justify-center gap-1.5 rounded-lg border px-4 py-2.5 text-center text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                <Trophy class="h-4 w-4" />
                                Lihat Peringkat
                            </Link>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>
