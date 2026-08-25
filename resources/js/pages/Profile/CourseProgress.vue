<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Check } from '@lucide/vue';
import profileRoutes from '@/routes/profile';

interface BlockProgress {
    id: number;
    type: string;
    title: string | null;
    sort_order: number;
    is_completed: boolean;
}

interface LessonProgress {
    id: number;
    title: string;
    sort_order: number;
    is_completed: boolean;
    blocks_completed: number;
    blocks_total: number;
    blocks: BlockProgress[];
}

interface ModuleProgress {
    id: number;
    title: string;
    lessons: LessonProgress[];
}

interface Course {
    id: number;
    title: string;
    slug: string;
}

interface Progress {
    totalLessons: number;
    completedLessons: number;
    percentage: number;
}

defineProps<{
    course: Course;
    modules: ModuleProgress[];
    progress: Progress;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Profil Saya', href: profileRoutes.index.url() },
            { title: 'Progress', href: '#' },
        ],
    },
});

function blockTypeLabel(type: string): string {
    const labels: Record<string, string> = {
        TEXT: 'Materi',
        CODE_EXAMPLE: 'Contoh Kode',
        HINT: 'Hint',
        MCQ_SINGLE: 'Pilihan Ganda',
        MCQ_MULTIPLE: 'Pilihan Ganda (Multi)',
        CODE_FILL: 'Lengkapi Kode',
        CODE_REORDER: 'Susun Kode',
        CODE_CHALLENGE: 'Tantangan Kode',
    };

    return labels[type] ?? type;
}
</script>

<template>
    <Head :title="`Progress — ${course.title}`" />

    <div class="mx-auto max-w-3xl p-4">
        <Link
            :href="profileRoutes.index.url()"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Profil
        </Link>

        <header class="mt-4">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                {{ course.title }}
            </h1>

            <div class="mt-4">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-700">Progress</span>
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
        </header>

        <div class="mt-8 space-y-6">
            <section
                v-for="mod in modules"
                :key="mod.id"
                class="overflow-hidden rounded-xl border bg-white"
            >
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ mod.title }}
                    </h2>
                </div>

                <div class="divide-y">
                    <div
                        v-for="lesson in mod.lessons"
                        :key="lesson.id"
                        class="px-6 py-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-medium"
                                :class="
                                    lesson.is_completed
                                        ? 'bg-green-100 text-green-700'
                                        : lesson.blocks_completed > 0
                                          ? 'bg-amber-100 text-amber-700'
                                          : 'bg-gray-100 text-gray-500'
                                "
                            >
                                <Check
                                    v-if="lesson.is_completed"
                                    class="h-4 w-4"
                                />
                                <span v-else>{{ lesson.sort_order }}</span>
                            </div>

                            <div class="flex-1">
                                <h3
                                    class="text-sm font-medium"
                                    :class="
                                        lesson.is_completed
                                            ? 'text-green-700'
                                            : 'text-gray-900'
                                    "
                                >
                                    {{ lesson.sort_order }}. {{ lesson.title }}
                                </h3>
                            </div>

                            <span
                                v-if="lesson.blocks_total > 0"
                                class="text-xs"
                                :class="
                                    lesson.is_completed
                                        ? 'text-green-600'
                                        : 'text-gray-400'
                                "
                            >
                                {{ lesson.blocks_completed }}/{{
                                    lesson.blocks_total
                                }}
                            </span>
                        </div>

                        <div
                            v-if="lesson.blocks.length > 0"
                            class="mt-2 ml-11 space-y-1"
                        >
                            <div
                                v-for="block in lesson.blocks"
                                :key="block.id"
                                class="flex items-center gap-2 text-xs"
                                :class="
                                    block.is_completed
                                        ? 'text-green-600'
                                        : 'text-gray-400'
                                "
                            >
                                <span
                                    class="inline-block h-1.5 w-1.5 shrink-0 rounded-full"
                                    :class="
                                        block.is_completed
                                            ? 'bg-green-500'
                                            : 'bg-gray-300'
                                    "
                                />
                                <span class="text-gray-500"
                                    >{{ block.sort_order }}.</span
                                >
                                <span
                                    >{{ blockTypeLabel(block.type)
                                    }}{{
                                        block.title ? ': ' + block.title : ''
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
