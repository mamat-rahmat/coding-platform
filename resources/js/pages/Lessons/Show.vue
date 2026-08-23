<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, CheckCircle2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
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

const page = usePage();

const gradedTypes = [
    'MCQ_SINGLE',
    'MCQ_MULTIPLE',
    'CODE_FILL',
    'CODE_REORDER',
    'CODE_CHALLENGE',
];

const isGraded = (block: LessonBlock) => gradedTypes.includes(block.type);

const answeredBlockIds = ref<Set<number>>(new Set());

function readBlockFromUrl(): number {
    if (props.lesson.blocks.length === 0) {
        return 0;
    }

    const params = new URLSearchParams(window.location.search);
    const block = Number(params.get('block'));

    if (!Number.isInteger(block) || block < 1) {
        return 0;
    }

    return Math.min(block - 1, props.lesson.blocks.length - 1);
}

const currentBlockIndex = ref(readBlockFromUrl());

function navigateToBlock(index: number) {
    currentBlockIndex.value = index;

    const url = lessonRoutes.show.url(props.lesson.slug, {
        query: { block: index + 1 },
    });

    router.get(url, {}, { preserveState: true, preserveScroll: true });
}

for (const block of props.lesson.blocks) {
    if ((block as LessonBlock & { is_answered?: boolean }).is_answered) {
        answeredBlockIds.value.add(block.id);
    }
}

watch(
    () => page.props.flash,
    (flash) => {
        const result = (
            flash as
                | {
                      attempt_result?: { block_id: number };
                  }
                | undefined
        )?.attempt_result;

        if (result) {
            answeredBlockIds.value.add(result.block_id);
        }
    },
    { deep: true },
);

const totalBlocks = computed(() => props.lesson.blocks.length);
const currentBlock = computed(
    () => props.lesson.blocks[currentBlockIndex.value],
);

const isLastBlock = computed(
    () => currentBlockIndex.value === totalBlocks.value - 1,
);

const isFirstBlock = computed(() => currentBlockIndex.value === 0);

const isCurrentGraded = computed(
    () => currentBlock.value && isGraded(currentBlock.value),
);

const isCurrentAnswered = computed(
    () =>
        !isCurrentGraded.value ||
        answeredBlockIds.value.has(currentBlock.value.id),
);

const canGoNext = computed(() => isCurrentAnswered.value);

const showManualComplete = computed(
    () =>
        !props.isCompleted &&
        props.lesson.blocks.filter((b) => gradedTypes.includes(b.type))
            .length === 0,
);

const progressPercentage = computed(() => {
    if (totalBlocks.value === 0) {
        return 0;
    }

    return Math.round(
        ((currentBlockIndex.value + 1) / totalBlocks.value) * 100,
    );
});

const blockProgressText = computed(
    () => `Block ${currentBlockIndex.value + 1} dari ${totalBlocks.value}`,
);

function goNext() {
    if (!canGoNext.value) {
        return;
    }

    if (isLastBlock.value) {
        return;
    }

    navigateToBlock(currentBlockIndex.value + 1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goPrev() {
    if (isFirstBlock.value) {
        return;
    }

    navigateToBlock(currentBlockIndex.value - 1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

watch(
    () => page.url,
    () => {
        currentBlockIndex.value = readBlockFromUrl();
    },
);

const completeLesson = (lessonSlug: string) => {
    router.post(
        lessonRoutes.complete.url(lessonSlug),
        {},
        { preserveScroll: true },
    );
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-3xl px-6 py-8">
            <!-- Breadcrumb -->
            <div class="mb-6 text-sm text-gray-500">
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
            <header class="mb-6">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    {{ lesson.title }}
                </h1>

                <p
                    v-if="lesson.description"
                    class="mt-2 text-base text-gray-600"
                >
                    {{ lesson.description }}
                </p>
            </header>

            <!-- Block Progress Bar -->
            <div class="mb-6">
                <div
                    class="mb-1.5 flex items-center justify-between text-xs text-gray-500"
                >
                    <span>{{ blockProgressText }}</span>
                    <span>{{ progressPercentage }}%</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                    <div
                        class="h-full rounded-full bg-gray-900 transition-all duration-300"
                        :style="{ width: `${progressPercentage}%` }"
                    />
                </div>
            </div>

            <!-- Current Block -->
            <main v-if="currentBlock">
                <section
                    :key="currentBlock.id"
                    class="rounded-xl border bg-white p-6 shadow-sm"
                >
                    <LessonBlockRenderer :block="currentBlock" />
                </section>
            </main>

            <!-- Block Navigation -->
            <div class="mt-6 flex items-center justify-between">
                <button
                    type="button"
                    :disabled="isFirstBlock"
                    class="flex items-center gap-1.5 rounded-lg border px-4 py-2.5 text-sm font-medium text-gray-700 transition enabled:hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                    @click="goPrev"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Prev
                </button>

                <!-- Next / Complete -->
                <template v-if="!isLastBlock">
                    <button
                        type="button"
                        :disabled="!canGoNext"
                        class="flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition enabled:hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="goNext"
                    >
                        Next
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </template>

                <template v-else>
                    <!-- Last block: show completion / lesson nav -->
                    <div class="flex items-center gap-2">
                        <button
                            v-if="showManualComplete"
                            type="button"
                            class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
                            @click="completeLesson(lesson.slug)"
                        >
                            Tandai selesai
                        </button>

                        <Link
                            v-else-if="isCompleted"
                            :href="courseRoutes.show.url(lesson.module.course.slug)"
                            class="flex items-center gap-1.5 rounded-lg bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            Lesson selesai — Kembali ke Course
                        </Link>

                        <span
                            v-else-if="isCurrentAnswered"
                            class="rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-600"
                        >
                            Beberapa soal belum benar — kembali ke Prev
                        </span>

                        <span v-else class="text-sm text-gray-500">
                            Jawab soal ini untuk menyelesaikan
                        </span>
                    </div>
                </template>
            </div>

            <!-- Lesson-level navigation (always visible at bottom) -->
            <div
                v-if="isLastBlock"
                class="mt-8 flex items-center justify-between border-t pt-4"
            >
                <Link
                    v-if="previousLesson"
                    :href="lessonRoutes.show.url(previousLesson.slug)"
                    class="text-sm text-gray-500 hover:text-gray-900"
                >
                    ← Lesson sebelumnya
                </Link>
                <div v-else />

                <Link
                    v-if="nextLesson && !isCompleted"
                    :href="lessonRoutes.show.url(nextLesson.slug)"
                    class="text-sm text-gray-500 hover:text-gray-900"
                >
                    Lesson berikutnya →
                </Link>
                <div v-else />
            </div>
        </div>
    </div>
</template>
