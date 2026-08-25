<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Pencil,
    ArrowLeft,
    Eye,
    Plus,
    Trash2,
    GripVertical,
    ListOrdered,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminBlockRoutes from '@/routes/admin/blocks';
import adminLessonRoutes from '@/routes/admin/lessons';
import lessonRoutes from '@/routes/lessons';

interface Block {
    id: number;
    type: string;
    title: string | null;
    sort_order: number;
    content: Record<string, unknown>;
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

const blockTypeLabels: Record<string, string> = {
    TEXT: 'Text (Markdown)',
    CODE_EXAMPLE: 'Code Example',
    HINT: 'Hint',
    MCQ_SINGLE: 'MCQ Single Answer',
    MCQ_MULTIPLE: 'MCQ Multiple Answer',
    CODE_FILL: 'Code Fill',
    CODE_REORDER: 'Code Reorder',
    CODE_CHALLENGE: 'Code Challenge',
};

function blockSummary(block: Block): string {
    const c = block.content;

    switch (block.type) {
        case 'TEXT':
            return typeof c.text === 'string'
                ? c.text.slice(0, 80)
                : '(kosong)';
        case 'CODE_EXAMPLE':
            return typeof c.code === 'string'
                ? c.code.slice(0, 80)
                : '(kosong)';
        case 'HINT':
            return typeof c.title === 'string' ? c.title : '(kosong)';
        case 'MCQ_SINGLE':
        case 'MCQ_MULTIPLE':
            return typeof c.question === 'string'
                ? c.question.slice(0, 80)
                : '(kosong)';
        case 'CODE_FILL':
            return typeof c.code_template === 'string'
                ? c.code_template.slice(0, 80)
                : '(kosong)';
        case 'CODE_REORDER':
            return Array.isArray(c.lines)
                ? `${c.lines.length} lines`
                : '(kosong)';
        case 'CODE_CHALLENGE':
            return typeof c.prompt === 'string'
                ? c.prompt.slice(0, 80)
                : '(kosong)';
        default:
            return '(unknown)';
    }
}

function destroy(block: Block) {
    if (!confirm(`Hapus block #${block.sort_order} (${block.type}?`)) {
        return;
    }

    router.delete(adminBlockRoutes.destroy.url(block.id));
}

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
                <div class="flex items-center justify-between">
                    <CardTitle>Blocks</CardTitle>

                    <div class="flex gap-2">
                        <Button as-child variant="outline" size="sm">
                            <Link
                                :href="
                                    adminBlockRoutes.index.url({
                                        lesson: lesson.id,
                                    })
                                "
                            >
                                <ListOrdered class="h-3.5 w-3.5" />
                                Kelola & Urutkan
                            </Link>
                        </Button>

                        <Button as-child size="sm">
                            <Link
                                :href="
                                    adminBlockRoutes.create.url({
                                        lesson: lesson.id,
                                    })
                                "
                            >
                                <Plus class="h-3.5 w-3.5" />
                                Block Baru
                            </Link>
                        </Button>
                    </div>
                </div>
            </CardHeader>

            <CardContent>
                <div
                    v-if="lesson.blocks.length === 0"
                    class="py-6 text-center text-sm text-gray-500"
                >
                    Belum ada block. Tambahkan melalui tombol Block Baru.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="block in lesson.blocks"
                        :key="block.id"
                        class="flex items-center gap-3 py-3"
                    >
                        <GripVertical class="h-4 w-4 shrink-0 text-gray-400" />

                        <div class="flex-1">
                            <div class="font-medium text-gray-900">
                                Block #{{ block.sort_order
                                }}<template v-if="block.title"
                                    >: {{ block.title }}</template
                                >
                            </div>
                            <div class="truncate text-xs text-gray-500">
                                {{ blockTypeLabels[block.type] ?? block.type }}
                                — {{ blockSummary(block) }}
                            </div>
                        </div>

                        <div class="flex gap-1">
                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminBlockRoutes.edit.url({
                                            block: block.id,
                                        })
                                    "
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon-sm"
                                @click="destroy(block)"
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
