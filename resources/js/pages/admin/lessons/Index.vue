<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye, ArrowLeft, MoveRight } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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

interface ModuleOption {
    id: number;
    title: string;
}

defineProps<{
    module: Module;
    modules: ModuleOption[];
}>();

const moveTarget = ref<Lesson | null>(null);
const moveModuleId = ref<string>('');
const isMoving = ref(false);

const moveDialogOpen = computed({
    get: () => moveTarget.value !== null,
    set: (val: boolean) => {
        if (!val) {
            moveTarget.value = null;
            moveModuleId.value = '';
        }
    },
});

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

function openMoveDialog(lesson: Lesson) {
    moveTarget.value = lesson;
    moveModuleId.value = '';
}

function moveLesson() {
    if (!moveTarget.value || !moveModuleId.value) {
        return;
    }

    isMoving.value = true;

    router.patch(
        adminLessonRoutes.move.url(moveTarget.value.id),
        { target_module_id: Number(moveModuleId.value) },
        {
            onFinish: () => {
                isMoving.value = false;
                moveTarget.value = null;
                moveModuleId.value = '';
            },
        },
    );
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
                                @click="openMoveDialog(lesson)"
                            >
                                <MoveRight class="h-4 w-4 text-blue-600" />
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

        <Dialog v-model:open="moveDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Pindahkan Lesson</DialogTitle>
                    <DialogDescription>
                        Pindahkan lesson
                        <template v-if="moveTarget">
                            <strong>{{ moveTarget.title }}</strong>
                        </template>
                        ke module lain di course yang sama.
                    </DialogDescription>
                </DialogHeader>

                <Select v-model="moveModuleId">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih module tujuan..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="m in modules"
                            :key="m.id"
                            :value="String(m.id)"
                            :disabled="m.id === module.id"
                        >
                            {{ m.title }}
                            <span
                                v-if="m.id === module.id"
                                class="text-xs text-gray-400"
                            >
                                (module saat ini)
                            </span>
                        </SelectItem>
                    </SelectContent>
                </Select>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Batal</Button>
                    </DialogClose>
                    <Button
                        :disabled="!moveModuleId || isMoving"
                        @click="moveLesson"
                    >
                        Pindahkan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
