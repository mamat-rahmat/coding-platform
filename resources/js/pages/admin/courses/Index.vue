<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye, Download, Upload } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminCourseRoutes from '@/routes/admin/courses';

interface Course {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    language: string;
    level: string;
    xp_reward: number;
    is_published: boolean;
    modules_count: number;
}

defineProps<{
    courses: Course[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Courses', href: adminCourseRoutes.index.url() },
        ],
    },
});

const importFile = ref<File | null>(null);
const isImporting = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

function destroy(course: Course) {
    if (!confirm(`Hapus course "${course.title}"?`)) {
        return;
    }

    router.delete(adminCourseRoutes.destroy.url(course.id));
}

function exportCourse(course: Course) {
    window.location.href = `/admin/courses/${course.id}/export`;
}

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    importFile.value = input.files?.[0] ?? null;
}

function importCourse() {
    if (!importFile.value) {
        return;
    }

    isImporting.value = true;

    const formData = new FormData();
    formData.append('file', importFile.value);

    router.post('/admin/courses/import', formData, {
        onFinish: () => {
            isImporting.value = false;
            importFile.value = null;
        },
    });
}
</script>

<template>
    <Head title="Admin - Courses" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Courses</h1>
                <p class="text-sm text-gray-500">Kelola semua course.</p>
            </div>

            <div class="flex gap-2">
                <div class="flex items-center gap-2">
                    <input
                        ref="fileInput"
                        id="import-file"
                        type="file"
                        accept=".json"
                        class="hidden"
                        @change="onFileChange"
                    />
                    <Button
                        variant="outline"
                        :disabled="isImporting"
                        @click="() => { importFile = null; fileInput?.click(); }"
                    >
                        <Upload class="h-4 w-4" />
                        {{ isImporting ? 'Mengimport...' : 'Import' }}
                    </Button>

                    <Button
                        v-if="importFile"
                        variant="default"
                        size="sm"
                        @click="importCourse"
                    >
                        Upload {{ importFile.name }}
                    </Button>
                </div>

                <Button as-child>
                    <Link :href="adminCourseRoutes.create.url()">
                        <Plus class="h-4 w-4" />
                        Course Baru
                    </Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Daftar Course</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="courses.length === 0"
                    class="py-8 text-center text-sm text-gray-500"
                >
                    Belum ada course.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="course in courses"
                        :key="course.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900">
                                    {{ course.title }}
                                </span>

                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        course.is_published
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-600'
                                    "
                                >
                                    {{
                                        course.is_published
                                            ? 'Published'
                                            : 'Draft'
                                    }}
                                </span>
                            </div>

                            <p class="text-xs text-gray-500">
                                {{ course.language }} · {{ course.level }} ·
                                {{ course.modules_count }} modules ·
                                {{ course.xp_reward }} XP
                            </p>
                        </div>

                        <div class="flex gap-1">
                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminCourseRoutes.show.url(course.id)
                                    "
                                >
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminCourseRoutes.edit.url(course.id)
                                    "
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon-sm"
                                @click="exportCourse(course)"
                            >
                                <Download class="h-4 w-4" />
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon-sm"
                                @click="destroy(course)"
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
