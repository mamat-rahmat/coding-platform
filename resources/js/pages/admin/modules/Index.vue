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

interface CourseOption {
    id: number;
    title: string;
}

defineProps<{
    course: Course;
    courses: CourseOption[];
}>();

const moveTarget = ref<Module | null>(null);
const moveCourseId = ref<string>('');
const isMoving = ref(false);

const moveDialogOpen = computed({
    get: () => moveTarget.value !== null,
    set: (val: boolean) => {
        if (!val) {
            moveTarget.value = null;
            moveCourseId.value = '';
        }
    },
});

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

function openMoveDialog(module: Module) {
    moveTarget.value = module;
    moveCourseId.value = '';
}

function moveModule() {
    if (!moveTarget.value || !moveCourseId.value) {
        return;
    }

    isMoving.value = true;

    router.patch(
        adminModuleRoutes.move.url(moveTarget.value.id),
        { target_course_id: Number(moveCourseId.value) },
        {
            onFinish: () => {
                isMoving.value = false;
                moveTarget.value = null;
                moveCourseId.value = '';
            },
        },
    );
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
                                @click="openMoveDialog(module)"
                            >
                                <MoveRight class="h-4 w-4 text-blue-600" />
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

        <Dialog v-model:open="moveDialogOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Pindahkan Module</DialogTitle>
                    <DialogDescription>
                        Pindahkan module
                        <template v-if="moveTarget">
                            <strong>{{ moveTarget.title }}</strong>
                        </template>
                        ke course lain.
                    </DialogDescription>
                </DialogHeader>

                <Select v-model="moveCourseId">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Pilih course tujuan..." />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="c in courses"
                            :key="c.id"
                            :value="String(c.id)"
                        >
                            {{ c.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Batal</Button>
                    </DialogClose>
                    <Button
                        :disabled="!moveCourseId || isMoving"
                        @click="moveModule"
                    >
                        Pindahkan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
