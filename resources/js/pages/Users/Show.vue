<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { User, BookOpen, Trophy } from '@lucide/vue';
import courseRoutes from '@/routes/courses';

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    language: string;
    level: string;
    xp_reward: number;
    total_lessons: number;
    completed_lessons: number;
    percentage: number;
}

interface ProfileUser {
    id: number;
    name: string;
    created_at: string;
}

defineProps<{
    profileUser: ProfileUser;
    courses: Course[];
    totalXp: number;
}>();

function formatDate(dateString: string): string {
    const date = new Date(dateString);

    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="`Profil — ${profileUser.name}`" />

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-5xl px-6 py-12">
            <Link
                :href="courseRoutes.index.url()"
                class="text-sm text-gray-500 hover:text-gray-900"
            >
                ← Kembali ke Courses
            </Link>

            <header class="mt-8">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-200"
                    >
                        <User class="h-8 w-8 text-gray-500" />
                    </div>

                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                            {{ profileUser.name }}
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Bergabung {{ formatDate(profileUser.created_at) }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex gap-6">
                    <div class="flex items-center gap-2">
                        <Trophy class="h-5 w-5 text-amber-500" />
                        <span class="text-sm font-medium text-gray-900">
                            {{ totalXp }} XP
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <BookOpen class="h-5 w-5 text-blue-500" />
                        <span class="text-sm font-medium text-gray-900">
                            {{ courses.length }} course diikuti
                        </span>
                    </div>
                </div>
            </header>

            <div class="mt-8">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Course</h2>

                <div
                    v-if="courses.length === 0"
                    class="rounded-xl border border-dashed bg-white p-12 text-center"
                >
                    <p class="text-gray-500">Belum ada course yang diikuti.</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="course in courses"
                        :key="course.id"
                        class="rounded-xl border bg-white p-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ course.title }}
                                    </h3>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            course.percentage === 100
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-gray-100 text-gray-600'
                                        "
                                    >
                                        {{ course.percentage === 100 ? 'Selesai' : 'Dalam progress' }}
                                    </span>
                                </div>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ course.language }} · {{ course.level }} ·
                                    {{ course.completed_lessons }}/{{ course.total_lessons }} lessons ·
                                    {{ course.xp_reward }} XP
                                </p>
                            </div>

                            <Link
                                :href="`/courses/${course.slug}`"
                                class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Buka Course
                            </Link>
                        </div>

                        <div class="mt-4">
                            <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                                <div
                                    class="h-full rounded-full bg-gray-900 transition-all"
                                    :style="{ width: `${course.percentage}%` }"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ course.percentage }}% selesai
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
