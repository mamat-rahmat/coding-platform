<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Lock, LockKeyhole, Pencil, Trophy } from '@lucide/vue';
import { computed } from 'vue';
import adminCourseRoutes from '@/routes/admin/courses';
import courseRoutes from '@/routes/courses';
import lessonRoutes from '@/routes/lessons';

interface Lesson {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_published: boolean;
    is_completed: boolean;
    is_locked: boolean;
    is_optional: boolean;
}

interface CourseModule {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_locked: boolean;
    is_completed: boolean;
    lessons: Lesson[];
}

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    language: string;
    level: string;
    thumbnail: string | null;
    xp_reward: number;
    modules: CourseModule[];
}

interface CourseProgress {
    totalLessons: number;
    completedLessons: number;
    percentage: number;
}

defineProps<{
    course: Course;
    progress: CourseProgress;
}>();

const page = usePage();

const isAdmin = computed(
    () => Boolean((page.props.auth?.user as { is_admin?: boolean })?.is_admin),
);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-5xl px-6 py-12">
            <Link
                :href="courseRoutes.index.url()"
                class="text-sm text-gray-500 hover:text-gray-900"
            >
                ← Kembali ke Courses
            </Link>

            <header class="mt-8">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                    >
                        {{ course.level }}
                    </span>

                    <span
                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                    >
                        {{ course.language }}
                    </span>

                    <span
                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                    >
                        {{ course.xp_reward }} XP
                    </span>
                </div>

                <h1
                    class="mt-4 text-4xl font-bold tracking-tight text-gray-900"
                >
                    {{ course.title }}
                </h1>

                <p class="mt-4 max-w-3xl text-lg text-gray-600">
                    {{ course.description }}
                </p>

                <div class="mt-6">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700">
                            Progress
                        </span>

                        <span class="text-gray-500">
                            {{ progress.completedLessons }} /
                            {{ progress.totalLessons }} lessons
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                        <div
                            class="h-full rounded-full bg-gray-900 transition-all"
                            :style="{ width: `${progress.percentage}%` }"
                        />
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ progress.percentage }}% selesai
                    </p>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <Link
                        :href="courseRoutes.leaderboard.url(course.slug)"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <Trophy class="h-4 w-4" />
                        Lihat Peringkat
                    </Link>

                    <Link
                        v-if="isAdmin"
                        :href="adminCourseRoutes.edit.url(course.id)"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <Pencil class="h-4 w-4" />
                        Edit Course
                    </Link>
                </div>
            </header>

            <div class="mt-12 space-y-6">
                <section
                    v-for="(module, moduleIndex) in course.modules"
                    :key="module.id"
                    class="overflow-hidden rounded-xl border bg-white"
                    :class="module.is_locked ? 'opacity-80' : ''"
                >
                    <div
                        class="flex items-start justify-between gap-4 border-b px-6 py-5"
                        :class="module.is_locked ? 'bg-gray-50' : ''"
                    >
                        <div>
                            <p
                                class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                            >
                                Module {{ moduleIndex + 1 }}
                            </p>

                            <h2
                                class="mt-1 text-xl font-semibold text-gray-900"
                            >
                                {{ module.title }}
                            </h2>

                            <p
                                v-if="module.description"
                                class="mt-1 text-sm text-gray-600"
                            >
                                {{ module.description }}
                            </p>
                        </div>

                        <span
                            v-if="module.is_locked"
                            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500"
                        >
                            <LockKeyhole class="h-3.5 w-3.5" />
                            Terkunci — selesaikan modul sebelumnya
                        </span>

                        <span
                            v-else-if="module.is_completed"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700"
                        >
                            Selesai
                        </span>
                    </div>

                    <div class="divide-y">
                        <div
                            v-for="(lesson, lessonIndex) in module.lessons"
                            :key="lesson.id"
                            class="flex items-center justify-between px-6 py-4"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-medium"
                                    :class="
                                        lesson.is_completed
                                            ? 'bg-green-100 text-green-700'
                                            : lesson.is_locked
                                              ? 'bg-gray-100 text-gray-400'
                                              : 'bg-gray-100 text-gray-600'
                                    "
                                >
                                    <span v-if="lesson.is_completed">✓</span>

                                    <Lock
                                        v-else-if="lesson.is_locked"
                                        class="h-4 w-4"
                                    />

                                    <span v-else>
                                        {{ lessonIndex + 1 }}
                                    </span>
                                </div>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium text-gray-900">
                                            {{ module.sort_order }}.{{ lesson.sort_order }} {{ lesson.title }}
                                        </h3>

                                        <span
                                            v-if="lesson.is_optional"
                                            class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700"
                                        >
                                            Opsional
                                        </span>
                                    </div>

                                    <p
                                        v-if="lesson.description"
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        {{ lesson.description }}
                                    </p>
                                </div>
                            </div>

                            <span
                                v-if="lesson.is_locked"
                                class="cursor-not-allowed rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-400"
                            >
                                Terkunci
                            </span>

                            <Link
                                v-else
                                :href="lessonRoutes.show.url(lesson.slug)"
                                class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-gray-50"
                                :class="
                                    lesson.is_completed
                                        ? 'border-green-200 text-green-700'
                                        : 'text-gray-700'
                                "
                            >
                                {{ lesson.is_completed ? 'Selesai' : 'Mulai' }}
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
