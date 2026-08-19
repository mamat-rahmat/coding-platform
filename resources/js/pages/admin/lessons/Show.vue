<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, ArrowLeft, Eye, Layers } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminLessonRoutes from '@/routes/admin/lessons';
import lessonRoutes from '@/routes/lessons';

interface Block {
    id: number;
    type: string;
    sort_order: number;
}

interface Lesson {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    sort_order: number;
    is_published: boolean;
    module: {
        id: number;
        title: string;
        course: { id: number; title: string };
    };
    blocks: Block[];
}

defineProps<{
    lesson: Lesson;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Lessons', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Lesson" />

    <div class="p-4">
        <Link
            :href="adminLessonRoutes.index.url({ module: lesson.module.id })"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Lessons
        </Link>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ lesson.title }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ lesson.module.course.title }} →
                    {{ lesson.module.title }} · Sort:
                    {{ lesson.sort_order }}
                </p>
                <p
                    v-if="lesson.description"
                    class="mt-2 max-w-2xl text-sm text-gray-600"
                >
                    {{ lesson.description }}
                </p>
            </div>

            <div class="flex gap-2">
                <Button as-child variant="outline" size="sm">
                    <Link :href="lessonRoutes.show.url(lesson.slug)">
                        <Eye class="h-3.5 w-3.5" />
                        Preview as Student
                    </Link>
                </Button>

                <Button as-child size="sm">
                    <Link :href="adminLessonRoutes.edit.url(lesson.id)">
                        <Pencil class="h-3.5 w-3.5" />
                        Edit
                    </Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Blocks</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="lesson.blocks.length === 0"
                    class="py-6 text-center text-sm text-gray-500"
                >
                    Belum ada block. CRUD block akan ditambahkan.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="block in lesson.blocks"
                        :key="block.id"
                        class="flex items-center gap-3 py-3"
                    >
                        <Layers class="h-4 w-4 text-gray-400" />

                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                Block #{{ block.sort_order }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Type: {{ block.type }}
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
