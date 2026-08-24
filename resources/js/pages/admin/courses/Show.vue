<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Pencil,
    ArrowLeft,
    Folder,
    Trash2,
    FolderPlus,
    Eye,
    Upload,
    Loader2,
} from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminCourseRoutes from '@/routes/admin/courses';
import adminModuleRoutes from '@/routes/admin/modules';
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

const props = defineProps<{
    course: Course;
}>();

const importFile = ref<File | null>(null);
const isImporting = ref(false);
const importMessage = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    importFile.value = target.files?.[0] ?? null;
    importMessage.value = null;
}

function importContent() {
    if (!importFile.value || isImporting.value) {
        return;
    }

    isImporting.value = true;
    importMessage.value = null;

    const formData = new FormData();
    formData.append('file', importFile.value);

    fetch(adminCourseRoutes.importContent.url(props.course.id), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': decodeURIComponent(
                document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
            ),
        },
        body: formData,
    })
        .then(async (res) => {
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message ?? 'Gagal import content.');
            }

            importMessage.value = `Berhasil: ${data.modules_added} module baru, ${data.modules_merged} module digabung, ${data.lessons_added} lesson baru, ${data.lessons_merged} lesson digabung, ${data.blocks_added} block baru, ${data.blocks_skipped} block dilewati.`;
            importFile.value = null;

            if (fileInput.value) {
                fileInput.value.value = '';
            }

            router.reload({ only: ['course'] });
        })
        .catch((err) => {
            importMessage.value = err.message ?? 'Gagal import content.';
        })
        .finally(() => {
            isImporting.value = false;
        });
}

function destroy(module: Module) {
    if (!confirm(`Hapus module "${module.title}"?`)) {
        return;
    }

    router.delete(adminModuleRoutes.destroy.url(module.id));
}

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
                <input
                    ref="fileInput"
                    type="file"
                    accept=".json"
                    class="hidden"
                    @change="onFileChange"
                />

                <Button
                    variant="outline"
                    size="sm"
                    :disabled="isImporting"
                    @click="fileInput?.click()"
                >
                    <Upload class="h-3.5 w-3.5" />
                    Import Content
                </Button>

                <Button
                    v-if="importFile"
                    size="sm"
                    :disabled="isImporting"
                    @click="importContent"
                >
                    <Loader2
                        v-if="isImporting"
                        class="h-3.5 w-3.5 animate-spin"
                    />
                    {{
                        isImporting
                            ? 'Mengimport...'
                            : `Upload ${importFile.name}`
                    }}
                </Button>

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

        <div
            v-if="importMessage"
            class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-700"
        >
            {{ importMessage }}
        </div>

        <Card>
            <CardHeader>
                <div class="flex items-center justify-between">
                    <CardTitle>Modules</CardTitle>

                    <Button as-child size="sm">
                        <Link
                            :href="
                                adminModuleRoutes.create.url({
                                    course: course.id,
                                })
                            "
                        >
                            <FolderPlus class="h-3.5 w-3.5" />
                            Module Baru
                        </Link>
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div
                    v-if="course.modules.length === 0"
                    class="py-6 text-center text-sm text-gray-500"
                >
                    Belum ada module.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="module in course.modules"
                        :key="module.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex items-center gap-3">
                            <Folder class="h-4 w-4 text-gray-400" />

                            <div>
                                <div class="font-medium text-gray-900">
                                    {{ module.title }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Sort order: {{ module.sort_order }} · /{{
                                        module.slug
                                    }}
                                </div>
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
