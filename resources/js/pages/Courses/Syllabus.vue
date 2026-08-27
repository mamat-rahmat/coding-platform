<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import courseRoutes from '@/routes/courses';
import lessonRoutes from '@/routes/lessons';

interface SyllabusBlock {
    id: number;
    type: string;
    title: string | null;
    sort_order: number;
    is_completed: boolean;
}

interface SyllabusLesson {
    id: number;
    title: string;
    slug: string;
    sort_order: number;
    is_optional: boolean;
    is_completed: boolean;
    blocks_completed: number;
    blocks_total: number;
    blocks: SyllabusBlock[];
}

interface SyllabusModule {
    id: number;
    title: string;
    lessons: SyllabusLesson[];
}

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    language: string;
    level: string;
    xp_reward: number;
}

defineProps<{
    course: Course;
    modules: SyllabusModule[];
}>();

const blockTypeLabels: Record<string, string> = {
    TEXT: 'Materi',
    CODE_EXAMPLE: 'Contoh Kode',
    HINT: 'Hint',
    MCQ_SINGLE: 'Pilihan Ganda',
    MCQ_MULTIPLE: 'Pilihan Ganda (Multi)',
    CODE_FILL: 'Lengkapi Kode',
    CODE_REORDER: 'Susun Kode',
    CODE_CHALLENGE: 'Tantangan Kode',
};

function blockLabel(block: SyllabusBlock): string {
    return block.title
        ? block.title
        : (blockTypeLabels[block.type] ?? block.type);
}
</script>

<template>
    <Head :title="`Silabus — ${course.title}`" />

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-5xl px-6 py-12">
            <Link
                :href="courseRoutes.show.url(course.slug)"
                class="text-sm text-gray-500 hover:text-gray-900"
            >
                ← Kembali ke Course
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
                    <Link
                        :href="courseRoutes.show.url(course.slug)"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Mulai / Lanjutkan Course
                    </Link>
                </div>
            </header>

            <div class="mt-12 space-y-6">
                <section
                    v-for="(module, moduleIndex) in modules"
                    :key="module.id"
                    class="overflow-hidden rounded-xl border bg-white"
                >
                    <div class="border-b px-6 py-5">
                        <p
                            class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                        >
                            Module {{ moduleIndex + 1 }}
                        </p>

                        <h2 class="mt-1 text-xl font-semibold text-gray-900">
                            {{ module.title }}
                        </h2>
                    </div>

                    <div class="divide-y">
                        <div
                            v-for="(lesson, lessonIndex) in module.lessons"
                            :key="lesson.id"
                            class="px-6 py-4"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-medium"
                                        :class="
                                            lesson.is_completed
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-gray-100 text-gray-600'
                                        "
                                    >
                                        <Check
                                            v-if="lesson.is_completed"
                                            class="h-4 w-4"
                                        />

                                        <span v-else>{{
                                            lessonIndex + 1
                                        }}</span>
                                    </div>

                                    <div>
                                        <h3 class="font-medium text-gray-900">
                                            {{ lesson.sort_order }}.
                                            {{ lesson.title }}
                                        </h3>

                                        <p
                                            v-if="lesson.is_optional"
                                            class="text-xs text-amber-600"
                                        >
                                            Opsional
                                        </p>
                                    </div>
                                </div>

                                <Link
                                    :href="lessonRoutes.show.url(lesson.slug)"
                                    class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-gray-50"
                                    :class="
                                        lesson.is_completed
                                            ? 'border-green-200 text-green-700'
                                            : 'text-gray-700'
                                    "
                                >
                                    {{
                                        lesson.is_completed
                                            ? 'Selesai'
                                            : 'Mulai'
                                    }}
                                </Link>
                            </div>

                            <div
                                v-if="lesson.blocks.length > 0"
                                class="mt-3 ml-12 space-y-1"
                            >
                                <div
                                    v-for="block in lesson.blocks"
                                    :key="block.id"
                                    class="flex items-center gap-2 text-xs"
                                >
                                    <span
                                        class="inline-block h-2 w-2 shrink-0 rounded-full"
                                        :class="
                                            block.is_completed
                                                ? 'bg-green-500'
                                                : 'bg-gray-300'
                                        "
                                    />
                                    <span class="text-gray-500"
                                        >{{ block.sort_order }}.</span
                                    >
                                    <span class="font-medium text-gray-700">
                                        {{ blockLabel(block) }}
                                    </span>
                                    <span
                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500"
                                    >
                                        {{
                                            blockTypeLabels[block.type] ??
                                            block.type
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
