<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, ArrowLeft, BookOpen } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminCourseRoutes from '@/routes/admin/courses';
import adminModuleRoutes from '@/routes/admin/modules';

interface Lesson {
    id: number;
    title: string;
    slug: string;
    sort_order: number;
    is_published: boolean;
}

interface Module {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    sort_order: number;
    lessons: Lesson[];
}

interface Course {
    id: number;
    title: string;
}

defineProps<{
    course: Course;
    module: Module;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            {
                title: 'Courses',
                href: adminCourseRoutes.index.url(),
            },
            { title: 'Modules', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Module" />

    <div class="p-4">
        <Link
            :href="adminModuleRoutes.index.url({ course: course.id })"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Modules
        </Link>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ module.title }}
                </h1>
                <p class="text-sm text-gray-500">
                    Sort order: {{ module.sort_order }}
                </p>
                <p
                    v-if="module.description"
                    class="mt-2 max-w-2xl text-sm text-gray-600"
                >
                    {{ module.description }}
                </p>
            </div>

            <Button as-child size="sm">
                <Link :href="adminModuleRoutes.edit.url(module.id)">
                    <Pencil class="h-3.5 w-3.5" />
                    Edit
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Lessons</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="module.lessons.length === 0"
                    class="py-6 text-center text-sm text-gray-500"
                >
                    Belum ada lesson. CRUD lesson akan ditambahkan.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="lesson in module.lessons"
                        :key="lesson.id"
                        class="flex items-center gap-3 py-3"
                    >
                        <BookOpen class="h-4 w-4 text-gray-400" />

                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                {{ lesson.sort_order }}. {{ lesson.title }}
                            </div>
                            <div class="text-xs text-gray-500">
                                /{{ lesson.slug }}
                            </div>
                        </div>

                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="
                                lesson.is_published
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600'
                            "
                        >
                            {{ lesson.is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
