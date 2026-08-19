<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye, ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminCourseRoutes from '@/routes/admin/courses';
import adminModuleRoutes from '@/routes/admin/modules';

interface Module {
    id: number;
    title: string;
    slug: string;
    sort_order: number;
}

interface Course {
    id: number;
    title: string;
    slug: string;
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
            { title: 'Modules', href: '#' },
        ],
    },
});

function destroy(module: Module) {
    if (!confirm(`Hapus module "${module.title}"?`)) {
        return;
    }

    router.delete(adminModuleRoutes.destroy.url(module.id));
}
</script>

<template>
    <Head title="Admin - Modules" />

    <div class="p-4">
        <Link
            :href="adminCourseRoutes.show.url(course.id)"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Course
        </Link>

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Modules — {{ course.title }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ course.modules_count }} modules ·
                    {{ course.lessons_count }} lessons total
                </p>
            </div>

            <Button as-child>
                <Link
                    :href="adminModuleRoutes.create.url({ course: course.id })"
                >
                    <Plus class="h-4 w-4" />
                    Module Baru
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Daftar Module</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="course.modules.length === 0"
                    class="py-8 text-center text-sm text-gray-500"
                >
                    Belum ada module.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="module in course.modules"
                        :key="module.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                {{ module.sort_order }}. {{ module.title }}
                            </div>
                            <div class="text-xs text-gray-500">
                                /{{ module.slug }}
                            </div>
                        </div>

                        <div class="flex gap-1">
                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminModuleRoutes.show.url(module.id)
                                    "
                                >
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminModuleRoutes.edit.url(module.id)
                                    "
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon-sm"
                                @click="destroy(module)"
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
