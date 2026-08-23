<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Check, Trophy } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import courseRoutes from '@/routes/courses';
import leaderboardRoutes from '@/routes/courses/leaderboard';

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

interface LeaderboardEntry {
    rank: number;
    user_id: number;
    uuid: string;
    name: string;
    completed_lessons: number;
    total_lessons: number;
    percentage: number;
    xp: number;
    is_current_user: boolean;
}

interface Course {
    id: number;
    title: string;
    slug: string;
}

const props = defineProps<{
    course: Course;
    leaderboard: LeaderboardEntry[];
    currentUserRank: number | null;
}>();

const isAuthenticated = computed(() => Boolean(usePage().props.auth?.user));

const expandedUserId = ref<number | null>(null);
const expandedLessonId = ref<number | null>(null);
const loadingUserId = ref<number | null>(null);
const userProgressCache = ref<Record<number, ModuleProgress[]>>({});

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

let pollInterval: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({
            only: ['leaderboard', 'currentUserRank'],
        });
    }, 5000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});

function toggleRow(entry: LeaderboardEntry) {
    if (expandedUserId.value === entry.user_id) {
        expandedUserId.value = null;
        expandedLessonId.value = null;

        return;
    }

    expandedUserId.value = entry.user_id;
    expandedLessonId.value = null;

    if (userProgressCache.value[entry.user_id]) {
        return;
    }

    loadingUserId.value = entry.user_id;

    const url = leaderboardRoutes.userProgress.url({
        course: props.course.slug,
        user: entry.user_id,
    });

    fetch(url)
        .then((res) => res.json())
        .then((data) => {
            userProgressCache.value[entry.user_id] = data.modules;
        })
        .finally(() => {
            loadingUserId.value = null;
        });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-5xl px-6 py-12">
            <Link
                v-if="isAuthenticated"
                :href="courseRoutes.show.url(course.slug)"
                class="text-sm text-gray-500 hover:text-gray-900"
            >
                ← Kembali ke {{ course.title }}
            </Link>

            <Link
                v-else
                :href="courseRoutes.index.url()"
                class="text-sm text-gray-500 hover:text-gray-900"
            >
                ← Kembali ke Courses
            </Link>

            <header class="mt-8">
                <div class="flex items-center gap-3">
                    <Trophy class="h-8 w-8 text-amber-500" />
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900">
                        Peringkat Peserta
                    </h1>
                </div>

                <p class="mt-2 text-lg text-gray-600">
                    {{ course.title }}
                </p>

                <p
                    v-if="currentUserRank !== null"
                    class="mt-4 inline-block rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Peringkat kamu: #{{ currentUserRank }}
                </p>
            </header>

            <div class="mt-8 overflow-hidden rounded-xl border bg-white">
                <div
                    v-if="leaderboard.length === 0"
                    class="px-6 py-12 text-center text-sm text-gray-500"
                >
                    Belum ada peserta yang menyelesaikan pelajaran.
                </div>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b bg-gray-50 text-left text-xs text-gray-500 uppercase"
                        >
                            <th class="px-6 py-3 font-medium">Peringkat</th>
                            <th class="px-6 py-3 font-medium">Peserta</th>
                            <th class="px-6 py-3 font-medium">Progress</th>
                            <th class="px-6 py-3 text-right font-medium">XP</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <template
                            v-for="entry in leaderboard"
                            :key="entry.user_id"
                        >
                            <tr
                                class="cursor-pointer transition-colors hover:bg-gray-50"
                                :class="[
                                    entry.is_current_user ? 'bg-amber-50' : '',
                                    expandedUserId === entry.user_id
                                        ? 'bg-gray-50'
                                        : '',
                                ]"
                                @click="toggleRow(entry)"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <ChevronDown
                                            class="h-4 w-4 text-gray-400 transition-transform"
                                            :class="
                                                expandedUserId === entry.user_id
                                                    ? 'rotate-180'
                                                    : ''
                                            "
                                        />
                                        <span
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full font-semibold"
                                            :class="
                                                entry.rank === 1
                                                    ? 'bg-amber-100 text-amber-700'
                                                    : entry.rank === 2
                                                      ? 'bg-gray-200 text-gray-700'
                                                      : entry.rank === 3
                                                        ? 'bg-orange-100 text-orange-700'
                                                        : 'bg-gray-100 text-gray-600'
                                            "
                                        >
                                            {{ entry.rank }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-medium text-gray-900">
                                    <Link
                                        :href="`/users/${entry.uuid}`"
                                        class="hover:underline"
                                    >
                                        {{ entry.name }}
                                    </Link>
                                    <span
                                        v-if="entry.is_current_user"
                                        class="ml-2 text-xs font-medium text-amber-700"
                                    >
                                        (kamu)
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-2 w-32 overflow-hidden rounded-full bg-gray-200"
                                        >
                                            <div
                                                class="h-full rounded-full bg-gray-900"
                                                :style="{
                                                    width: `${entry.percentage}%`,
                                                }"
                                            />
                                        </div>

                                        <span class="text-xs text-gray-500">
                                            {{
                                                entry.completed_lessons
                                            }}
                                            /
                                            {{ entry.total_lessons }}
                                            lessons ({{
                                                entry.percentage
                                            }}%)
                                        </span>
                                    </div>
                                </td>

                                <td
                                    class="px-6 py-4 text-right font-medium text-gray-900"
                                >
                                    {{ entry.xp }} XP
                                </td>
                            </tr>

                            <tr
                                v-if="expandedUserId === entry.user_id"
                                :key="`detail-${entry.user_id}`"
                            >
                                <td colspan="4" class="bg-gray-50 px-6 py-4">
                                    <div
                                        v-if="
                                            loadingUserId === entry.user_id
                                        "
                                        class="py-4 text-center text-sm text-gray-500"
                                    >
                                        Memuat detail pelajaran...
                                    </div>

                                    <div
                                        v-else-if="
                                            userProgressCache[entry.user_id]
                                        "
                                        class="space-y-4"
                                    >
                                        <div
                                            v-for="mod in userProgressCache[entry.user_id]"
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
                                                            v-if="
                                                                lesson.is_completed
                                                            "
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
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
