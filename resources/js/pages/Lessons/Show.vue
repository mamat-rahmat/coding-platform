<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import LessonBlockRenderer from '@/components/lesson/LessonBlockRenderer.vue';
import courseRoutes from '@/routes/courses';
import lessonRoutes from '@/routes/lessons';
import type { LessonBlock } from '@/types/lesson';

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

interface LessonSummary {
    id: number;
    title: string;
    slug: string;
}

interface BlockStatus {
    totalGraded: number;
    correctGraded: number;
    allCorrect: boolean;
}

const props = defineProps<{
    lesson: Lesson;
    previousLesson: LessonSummary | null;
    nextLesson: LessonSummary | null;
    isCompleted: boolean;
    blockStatus: BlockStatus;
}>();

const hasGradedBlocks = computed(() => props.blockStatus.totalGraded > 0);

const showManualComplete = computed(
    () => !props.isCompleted && !hasGradedBlocks.value,
);

const progressPercentage = computed(() => {
    if (props.blockStatus.totalGraded === 0) {
        return props.isCompleted ? 100 : 0;
    }

    return Math.round(
        (props.blockStatus.correctGraded / props.blockStatus.totalGraded) * 100,
    );
});

const completeLesson = (lessonSlug: string) => {
    router.post(
        lessonRoutes.complete.url(lessonSlug),
        {},
        {
            preserveScroll: true,
        },
    );
};
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
            <header class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    {{ lesson.title }}
                </h1>

                <p v-if="lesson.description" class="mt-3 text-lg text-gray-600">
                    {{ lesson.description }}
                </p>

                <div v-if="hasGradedBlocks" class="mt-4">
                    <div
                        class="mb-1 flex items-center justify-between text-xs text-gray-500"
                    >
                        <span>Progress soal</span>

                        <span>
                            {{ blockStatus.correctGraded }} /
                            {{ blockStatus.totalGraded }} benar
                        </span>
                    </div>

                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-200">
                        <div
                            class="h-full rounded-full bg-green-500 transition-all"
                            :style="{ width: `${progressPercentage}%` }"
                        />
                    </div>
                </div>
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
            <div class="mt-10 flex items-center justify-between border-t pt-6">
                <Link
                    v-if="previousLesson"
                    :href="lessonRoutes.show.url(previousLesson.slug)"
                    class="rounded-lg border px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    ← Sebelumnya
                </Link>

                <div v-else />

                <button
                    v-if="showManualComplete"
                    type="button"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
                    @click="completeLesson(lesson.slug)"
                >
                    Tandai selesai
                </button>

                <Link
                    v-else-if="isCompleted && nextLesson"
                    :href="lessonRoutes.show.url(nextLesson.slug)"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
                >
                    Lanjut →
                </Link>

                <span
                    v-else-if="isCompleted"
                    class="rounded-lg bg-green-100 px-5 py-2.5 text-sm font-medium text-green-700"
                >
                    ✓ Lesson selesai
                </span>

                <span
                    v-else
                    class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-500"
                >
                    Selesaikan semua soal untuk lanjut
                </span>
            </div>
        </div>
    </div>
</template>
