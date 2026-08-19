<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye, ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
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
    course: { id: number; title: string };
    lessons: Lesson[];
}

defineProps<{
    module: Module;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Lessons', href: '#' },
        ],
    },
});

function destroy(lesson: Lesson) {
    if (!confirm(`Hapus lesson "${lesson.title}"?`)) {
        return;
    }

    router.delete(adminLessonRoutes.destroy.url(lesson.id));
}
</script>

<template>
    <Head title="Admin - Lessons" />

    <div class="p-4">
        <Link
            :href="adminModuleRoutes.show.url(module.id)"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Module
        </Link>

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Lessons — {{ module.title }}
                </h1>
                <p class="text-sm text-gray-500">
                    Course: {{ module.course.title }}
                </p>
            </div>

            <Button as-child>
                <Link
                    :href="adminLessonRoutes.create.url({ module: module.id })"
                >
                    <Plus class="h-4 w-4" />
                    Lesson Baru
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Daftar Lesson</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="module.lessons.length === 0"
                    class="py-8 text-center text-sm text-gray-500"
                >
                    Belum ada lesson.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="lesson in module.lessons"
                        :key="lesson.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                {{ lesson.sort_order }}. {{ lesson.title }}
                            </div>
                            <div class="text-xs text-gray-500">
                                /{{ lesson.slug }}
                            </div>
                        </div>

                        <span
                            class="mr-3 rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="
                                lesson.is_published
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600'
                            "
                        >
                            {{ lesson.is_published ? 'Published' : 'Draft' }}
                        </span>

                        <div class="flex gap-1">
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
