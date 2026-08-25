<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import profileRoutes from '@/routes/profile';

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

defineProps<{
    courses: Course[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Profil Saya', href: profileRoutes.index.url() },
        ],
    },
});
</script>

<template>
    <Head title="Profil Saya" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Profil Saya</h1>
            <p class="text-sm text-gray-500">Course yang kamu ikuti.</p>
        </div>

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
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ course.title }}
                            </h2>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    course.percentage === 100
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-600'
                                "
                            >
                                {{
                                    course.percentage === 100
                                        ? 'Selesai'
                                        : 'Dalam progress'
                                }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ course.language }} · {{ course.level }} ·
                            {{ course.completed_lessons }}/{{
                                course.total_lessons
                            }}
                            lessons · {{ course.xp_reward }} XP
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            :href="
                                profileRoutes['courseProgress'].url(course.slug)
                            "
                            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
                        >
                            Lihat Progress
                        </Link>

                        <Link
                            :href="`/courses/${course.slug}`"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Buka Course
                        </Link>
                    </div>
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
</template>
