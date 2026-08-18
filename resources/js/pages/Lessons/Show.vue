<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import courseRoutes from '@/routes/courses';
import LessonBlockRenderer from '@/components/lesson/LessonBlockRenderer.vue';

interface LessonBlock {
    id: number;
    type: string;
    content: {
        text?: string;
        language?: string;
        code?: string;
    };
    sort_order: number;
}

interface Lesson {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    blocks: LessonBlock[];
    module: {
        id: number;
        title: string;
        course: {
            id: number;
            title: string;
            slug: string;
        };
    };
}

defineProps<{
    lesson: Lesson;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-4xl px-6 py-10">

            <!-- Breadcrumb -->
            <div class="mb-8 text-sm text-gray-500">
                <Link
                    :href="courseRoutes.show.url(lesson.module.course.slug)"
                    class="hover:text-gray-900"
                >
                    {{ lesson.module.course.title }}
                </Link>

                <span class="mx-2">/</span>

                <span>{{ lesson.module.title }}</span>
            </div>

            <!-- Lesson Header -->
            <header class="mb-10">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    {{ lesson.title }}
                </h1>

                <p
                    v-if="lesson.description"
                    class="mt-3 text-lg text-gray-600"
                >
                    {{ lesson.description }}
                </p>
            </header>

            <!-- Lesson Blocks -->
            <main class="space-y-8">
                <section
                    v-for="block in lesson.blocks"
                    :key="block.id"
                    class="rounded-xl border bg-white p-6 shadow-sm"
                >
                    <LessonBlockRenderer :block="block" />
                </section>
            </main>

            <!-- Navigation -->
            <div class="mt-10 flex justify-end border-t pt-6">
                <button
                    type="button"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
                >
                    Lanjut →
                </button>
            </div>
        </div>
    </div>
</template>