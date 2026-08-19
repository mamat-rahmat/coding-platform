<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, ArrowLeft, Folder } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminCourseRoutes from '@/routes/admin/courses';
import courseRoutes from '@/routes/courses';

interface Module {
    id: number;
    title: string;
    slug: string;
    sort_order: number;
    lessons_count?: number;
}

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    language: string;
    level: string;
    thumbnail: string | null;
    xp_reward: number;
    is_published: boolean;
    modules: Module[];
    modules_count: number;
    lessons_count: number;
}

defineProps<{
    course: Course;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            {
                title: 'Courses',
                href: adminCourseRoutes.index.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Course" />

    <div class="p-4">
        <Link
            :href="adminCourseRoutes.index.url()"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Courses
        </Link>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ course.title }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ course.language }} · {{ course.level }} ·
                    {{ course.modules_count }} modules ·
                    {{ course.lessons_count }} lessons ·
                    {{ course.xp_reward }} XP
                </p>
                <p
                    v-if="course.description"
                    class="mt-2 max-w-2xl text-sm text-gray-600"
                >
                    {{ course.description }}
                </p>
            </div>

            <div class="flex gap-2">
                <Button as-child variant="outline" size="sm">
                    <Link :href="courseRoutes.show.url(course.slug)">
                        Preview as Student
                    </Link>
                </Button>

                <Button as-child size="sm">
                    <Link :href="adminCourseRoutes.edit.url(course.id)">
                        <Pencil class="h-3.5 w-3.5" />
                        Edit
                    </Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Modules</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="course.modules.length === 0"
                    class="py-6 text-center text-sm text-gray-500"
                >
                    Belum ada module. CRUD module akan ditambahkan.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="module in course.modules"
                        :key="module.id"
                        class="flex items-center gap-3 py-3"
                    >
                        <Folder class="h-4 w-4 text-gray-400" />

                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                {{ module.title }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Sort order: {{ module.sort_order }}
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
