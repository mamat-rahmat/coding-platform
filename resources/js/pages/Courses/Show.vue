<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import courseRoutes from '@/routes/courses';
import lessonRoutes from '@/routes/lessons';

interface Lesson {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    sort_order: number;
}

interface CourseModule {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    sort_order: number;
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

defineProps<{
    course: Course;
}>();
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

                <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-900">
                    {{ course.title }}
                </h1>

                <p class="mt-4 max-w-3xl text-lg text-gray-600">
                    {{ course.description }}
                </p>
            </header>

            <div class="mt-12 space-y-6">
                <section
                    v-for="(module, moduleIndex) in course.modules"
                    :key="module.id"
                    class="overflow-hidden rounded-xl border bg-white"
                >
                    <div class="border-b px-6 py-5">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Module {{ moduleIndex + 1 }}
                        </p>

                        <h2 class="mt-1 text-xl font-semibold text-gray-900">
                            {{ module.title }}
                        </h2>

                        <p
                            v-if="module.description"
                            class="mt-1 text-sm text-gray-600"
                        >
                            {{ module.description }}
                        </p>
                    </div>

                    <div class="divide-y">
                        <div
                            v-for="(lesson, lessonIndex) in module.lessons"
                            :key="lesson.id"
                            class="flex items-center justify-between px-6 py-4"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-sm font-medium text-gray-600"
                                >
                                    {{ lessonIndex + 1 }}
                                </div>

                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        {{ lesson.title }}
                                    </h3>

                                    <p
                                        v-if="lesson.description"
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        {{ lesson.description }}
                                    </p>
                                </div>
                            </div>

                            <Link
                                :href="lessonRoutes.show.url(lesson.slug)"
                                class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Mulai
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>