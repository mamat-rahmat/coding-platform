<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';

interface Stats {
    courses: number;
    publishedCourses: number;
    lessons: number;
    publishedLessons: number;
    blocks: number;
    blockTypeCounts: Record<string, number>;
    users: number;
    admins: number;
    attempts: number;
    correctAttempts: number;
}

defineProps<{
    stats: Stats;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin',
                href: adminRoutes.dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Admin Dashboard</h1>
            <p class="text-sm text-gray-500">Ringkasan statistik platform.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Courses</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.courses }}</div>
                    <p class="text-xs text-gray-500">
                        {{ stats.publishedCourses }} published
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Lessons</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.lessons }}</div>
                    <p class="text-xs text-gray-500">
                        {{ stats.publishedLessons }} published
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Blocks</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.blocks }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Users</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.users }}</div>
                    <p class="text-xs text-gray-500">
                        {{ stats.admins }} admins
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium">
                    Blocks per Type
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(count, type) in stats.blockTypeCounts"
                        :key="type"
                        class="rounded-lg border p-3"
                    >
                        <div
                            class="text-xs font-medium text-gray-500 uppercase"
                        >
                            {{ type }}
                        </div>
                        <div class="text-xl font-bold">{{ count }}</div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="text-sm font-medium">Attempts</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex gap-8">
                    <div>
                        <div class="text-2xl font-bold">
                            {{ stats.attempts }}
                        </div>
                        <p class="text-xs text-gray-500">Total attempts</p>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600">
                            {{ stats.correctAttempts }}
                        </div>
                        <p class="text-xs text-gray-500">Correct</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
