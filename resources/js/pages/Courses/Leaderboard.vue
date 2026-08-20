<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Trophy } from '@lucide/vue';
import courseRoutes from '@/routes/courses';

interface LeaderboardEntry {
    rank: number;
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

defineProps<{
    course: Course;
    leaderboard: LeaderboardEntry[];
    currentUserRank: number | null;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-5xl px-6 py-12">
            <Link
                :href="courseRoutes.show.url(course.slug)"
                class="text-sm text-gray-500 hover:text-gray-900"
            >
                ← Kembali ke {{ course.title }}
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
                        <tr
                            v-for="entry in leaderboard"
                            :key="entry.name"
                            class="hover:bg-gray-50"
                            :class="entry.is_current_user ? 'bg-amber-50' : ''"
                        >
                            <td class="px-6 py-4">
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
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ entry.name }}
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
                                        {{ entry.completed_lessons }} /
                                        {{ entry.total_lessons }} lessons ({{
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
