<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    ArrowLeft,
    GripVertical,
    Eye,
    Save,
} from '@lucide/vue';
import { ref } from 'vue';
import draggable from 'vuedraggable';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import adminRoutes from '@/routes/admin';
import adminBlockRoutes from '@/routes/admin/blocks';
import adminLessonRoutes from '@/routes/admin/lessons';

interface Block {
    id: number;
    type: string;
    sort_order: number;
    content: Record<string, unknown>;
}

interface Lesson {
    id: number;
    title: string;
    slug: string;
    module: {
        id: number;
        title: string;
        course: { id: number; title: string };
    };
    blocks: Block[];
}

const props = defineProps<{
    lesson: Lesson;
}>();

const blocks = ref([...props.lesson.blocks]);
const isDirty = ref(false);
const isSaving = ref(false);

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

function onDragEnd() {
    blocks.value.forEach((b, i) => {
        b.sort_order = i + 1;
    });
    isDirty.value = true;
}

function saveOrder() {
    isSaving.value = true;

    router.patch(
        adminBlockRoutes.reorder.url(props.lesson.id),
        {
            blocks: blocks.value.map((b) => ({
                id: b.id,
                sort_order: b.sort_order,
            })),
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isSaving.value = false;
                isDirty.value = false;
            },
        },
    );
}

function destroy(block: Block) {
    if (!confirm(`Hapus block #${block.sort_order} (${block.type}?`)) {
        return;
    }

    router.delete(adminBlockRoutes.destroy.url(block.id));
}

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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Blocks', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Blocks" />

    <div class="p-4">
        <Link
            :href="adminLessonRoutes.show.url(lesson.id)"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Lesson
        </Link>

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Blocks — {{ lesson.title }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ lesson.module.course.title }} →
                    {{ lesson.module.title }} · {{ blocks.length }} blocks
                </p>
            </div>

            <div class="flex gap-2">
                <Button as-child variant="outline" size="sm">
                    <Link :href="`/lessons/${lesson.slug}`">
                        <Eye class="h-3.5 w-3.5" />
                        Preview as Student
                    </Link>
                </Button>

                <Button
                    v-if="isDirty"
                    size="sm"
                    :disabled="isSaving"
                    @click="saveOrder"
                >
                    <Save class="h-3.5 w-3.5" />
                    Save Order
                </Button>

                <Button as-child size="sm">
                    <Link
                        :href="
                            adminBlockRoutes.create.url({ lesson: lesson.id })
                        "
                    >
                        <Plus class="h-3.5 w-3.5" />
                        Block Baru
                    </Link>
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Daftar Block (seret untuk reorder)</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="blocks.length === 0"
                    class="py-8 text-center text-sm text-gray-500"
                >
                    Belum ada block.
                </div>

                <draggable
                    v-else
                    v-model="blocks"
                    :item-key="(item: Block) => String(item.id)"
                    handle=".drag-handle"
                    class="space-y-2"
                    @end="onDragEnd"
                >
                    <template #item="{ element }">
                        <div
                            class="flex items-center gap-3 rounded-lg border bg-white p-3"
                        >
                            <span
                                class="drag-handle flex h-6 w-6 shrink-0 cursor-move items-center justify-center text-gray-400"
                            >
                                <GripVertical class="h-4 w-4" />
                            </span>

                            <span class="font-mono text-xs text-gray-500">
                                #{{ element.sort_order }}
                            </span>

                            <div class="flex-1">
                                <div class="text-xs font-medium text-gray-900">
                                    {{
                                        blockTypeLabels[element.type] ??
                                        element.type
                                    }}
                                </div>
                                <div class="truncate text-xs text-gray-500">
                                    {{ blockSummary(element) }}
                                </div>
                            </div>

                            <div class="flex gap-1">
                                <Button as-child variant="ghost" size="icon-sm">
                                    <Link
                                        :href="
                                            adminBlockRoutes.edit.url({
                                                block: element.id,
                                            })
                                        "
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>

                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    @click="destroy(element)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </div>
                    </template>
                </draggable>
            </CardContent>
        </Card>
    </div>
</template>
