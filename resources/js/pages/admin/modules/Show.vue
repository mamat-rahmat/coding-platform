<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, ArrowLeft, BookOpen, Plus, Trash2, Eye } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminCourseRoutes from '@/routes/admin/courses';
import adminLessonRoutes from '@/routes/admin/lessons';
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

function destroy(lesson: Lesson) {
    if (!confirm(`Hapus lesson "${lesson.title}"?`)) {
        return;
    }

    router.delete(adminLessonRoutes.destroy.url(lesson.id));
}

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

            <div class="flex gap-2">
                <Button as-child variant="outline" size="sm">
                    <Link
                        :href="
                            adminLessonRoutes.index.url({ module: module.id })
                        "
                    >
                        Kelola Lessons
                    </Link>
                </Button>

                <Button as-child size="sm">
                    <Link :href="adminModuleRoutes.edit.url(module.id)">
                        <Pencil class="h-3.5 w-3.5" />
                        Edit
                    </Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>Lessons</CardTitle>

                    <Button as-child size="sm">
                        <Link
                            :href="
                                adminLessonRoutes.create.url({
                                    module: module.id,
                                })
                            "
                        >
                            <Plus class="h-3.5 w-3.5" />
                            Lesson Baru
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div
                    v-if="module.lessons.length === 0"
                    class="py-6 text-center text-sm text-gray-500"
                >
                    Belum ada lesson.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="lesson in module.lessons"
                        :key="lesson.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex items-center gap-3">
                            <BookOpen class="h-4 w-4 text-gray-400" />

                            <div>
                                <div class="font-medium text-gray-900">
                                    {{ lesson.sort_order }}. {{ lesson.title }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    /{{ lesson.slug }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <span
                                class="mr-2 rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    lesson.is_published
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-600'
                                "
                            >
                                {{
                                    lesson.is_published ? 'Published' : 'Draft'
                                }}
                            </span>

                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminLessonRoutes.show.url(lesson.id)
                                    "
                                >
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminLessonRoutes.edit.url(lesson.id)
                                    "
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon-sm"
                                @click="destroy(lesson)"
                            >
                                <Trash2 class="h-4 w-4 text-red-600" />
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
