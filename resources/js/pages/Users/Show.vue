<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Check, User, BookOpen, Trophy } from '@lucide/vue';
import { ref } from 'vue';
import courseRoutes from '@/routes/courses';
import userRoutes from '@/routes/users';

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

const props = defineProps<{
    profileUser: ProfileUser;
    courses: Course[];
    totalXp: number;
}>();

const expandedCourseId = ref<number | null>(null);
const expandedLessonId = ref<number | null>(null);
const loadingCourseId = ref<number | null>(null);
const courseProgressCache = ref<Record<number, ModuleProgress[]>>({});

function toggleCourse(course: Course) {
    if (expandedCourseId.value === course.id) {
        expandedCourseId.value = null;
        expandedLessonId.value = null;

        return;
    }

    expandedCourseId.value = course.id;
    expandedLessonId.value = null;

    if (courseProgressCache.value[course.id]) {
        return;
    }

    loadingCourseId.value = course.id;

    const url = userRoutes.courseProgress.url({
        user: props.profileUser.id,
        course: course.slug,
    });

    fetch(url)
        .then((res) => res.json())
        .then((data) => {
            courseProgressCache.value[course.id] = data.modules;
        })
        .finally(() => {
            loadingCourseId.value = null;
        });
}

function toggleLesson(lessonId: number) {
    expandedLessonId.value =
        expandedLessonId.value === lessonId ? null : lessonId;
}

function blockTypeLabel(type: string): string {
    const labels: Record<string, string> = {
        TEXT: 'Materi',
        CODE_EXAMPLE: 'Contoh Kode',
        HINT: 'Hint',
        MCQ_SINGLE: 'Pilihan Ganda',
        MCQ_MULTIPLE: 'Pilihan Ganda (Multi)',
        CODE_FILL: 'Isi Kode',
        CODE_REORDER: 'Susun Kode',
        CODE_CHALLENGE: 'Tantangan Kode',
    };

    return labels[type] ?? type;
}

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
                    <template
                        v-for="course in courses"
                        :key="course.id"
                    >
                        <div class="overflow-hidden rounded-xl border bg-white">
                            <div
                                class="cursor-pointer p-5 transition-colors hover:bg-gray-50"
                                @click="toggleCourse(course)"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-2">
                                        <ChevronDown
                                            class="h-4 w-4 shrink-0 text-gray-400 transition-transform"
                                            :class="
                                                expandedCourseId === course.id
                                                    ? 'rotate-180'
                                                    : ''
                                            "
                                        />
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

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500">
                                            {{ course.completed_lessons }}/{{ course.total_lessons }} lessons
                                        </span>

                                        <Link
                                            :href="`/courses/${course.slug}`"
                                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                            @click.stop
                                        >
                                            Buka Course
                                        </Link>
                                    </div>
                                </div>

                                <p class="mt-1 ml-6 text-sm text-gray-500">
                                    {{ course.language }} · {{ course.level }} · {{ course.xp_reward }} XP
                                </p>

                                <div class="mt-3 ml-6">
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

                            <div
                                v-if="expandedCourseId === course.id"
                                class="border-t bg-gray-50 px-6 py-4"
                            >
                                <div
                                    v-if="loadingCourseId === course.id"
                                    class="py-4 text-center text-sm text-gray-500"
                                >
                                    Memuat detail pelajaran...
                                </div>

                                <div
                                    v-else-if="courseProgressCache[course.id]"
                                    class="space-y-4"
                                >
                                    <div
                                        v-for="mod in courseProgressCache[course.id]"
                                        :key="mod.id"
                                    >
                                        <h4
                                            class="mb-2 text-xs font-semibold tracking-wide text-gray-500 uppercase"
                                        >
                                            {{ mod.title }}
                                        </h4>

                                        <div class="pl-4">
                                            <div
                                                v-for="lesson in mod.lessons"
                                                :key="lesson.id"
                                            >
                                                <div
                                                    class="flex min-w-0 cursor-pointer items-center gap-2 rounded-md px-3 py-1.5 text-sm transition-colors hover:bg-gray-100"
                                                    :class="
                                                        lesson.is_completed
                                                            ? 'bg-green-50 text-green-700'
                                                            : lesson.blocks_completed > 0
                                                              ? 'bg-amber-50 text-amber-700'
                                                              : 'text-gray-500'
                                                    "
                                                    @click="toggleLesson(lesson.id)"
                                                >
                                                    <ChevronRight
                                                        class="h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform"
                                                        :class="
                                                            expandedLessonId === lesson.id
                                                                ? 'rotate-90'
                                                                : ''
                                                        "
                                                    />
                                                    <Check
                                                        v-if="lesson.is_completed"
                                                        class="h-3.5 w-3.5 shrink-0 text-green-600"
                                                    />
                                                    <span
                                                        v-else
                                                        class="inline-block h-3.5 w-3.5 shrink-0 rounded-full border-2"
                                                        :class="
                                                            lesson.blocks_completed > 0
                                                                ? 'border-amber-400'
                                                                : 'border-gray-300'
                                                        "
                                                    />
                                                    <span class="truncate">{{ lesson.sort_order }} {{ lesson.title }}</span>
                                                    <span
                                                        v-if="lesson.blocks_total > 0"
                                                        class="ml-auto shrink-0 text-xs"
                                                        :class="
                                                            lesson.is_completed
                                                                ? 'text-green-600'
                                                                : 'text-gray-400'
                                                        "
                                                    >
                                                        {{ lesson.blocks_completed }}/{{ lesson.blocks_total }}
                                                    </span>
                                                </div>

                                                <div
                                                    v-if="expandedLessonId === lesson.id && lesson.blocks.length > 0"
                                                    class="ml-8 mt-1 space-y-0.5"
                                                >
                                                    <div
                                                        v-for="block in lesson.blocks"
                                                        :key="block.id"
                                                        class="flex items-center gap-2 rounded px-3 py-1 text-xs"
                                                        :class="
                                                            block.is_completed
                                                                ? 'text-green-600'
                                                                : 'text-gray-400'
                                                        "
                                                    >
                                                        <span
                                                            class="inline-block h-2 w-2 shrink-0 rounded-full"
                                                            :class="
                                                                block.is_completed
                                                                    ? 'bg-green-500'
                                                                    : 'bg-gray-300'
                                                            "
                                                        />
                                                        <span class="text-gray-500">{{ block.sort_order }}</span>
                                                        <span>{{ blockTypeLabel(block.type) }}{{ block.title ? ': ' + block.title : '' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
