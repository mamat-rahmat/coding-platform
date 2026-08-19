<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Eye } from '@lucide/vue';
import { reactive } from 'vue';
import BlockEditorDispatcher from '@/components/admin/BlockEditorDispatcher.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import adminRoutes from '@/routes/admin';
import adminBlockRoutes from '@/routes/admin/blocks';
import lessonRoutes from '@/routes/lessons';

interface Lesson {
    id: number;
    title: string;
    slug: string;
}

interface Block {
    id: number;
    type: string;
    content: Record<string, unknown>;
    sort_order: number;
}

const props = defineProps<{
    lesson: Lesson;
    block: Block;
}>();

const form = reactive({
    type: props.block.type,
    content: { ...props.block.content },
    sort_order: props.block.sort_order,
});

const errors = reactive<Record<string, string>>({});

function submit() {
    router.put(adminBlockRoutes.update.url(props.block.id), form, {
        preserveScroll: true,
        onError: (serverErrors) => {
            Object.assign(errors, serverErrors);
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Blocks', href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Edit Block" />

    <div class="mx-auto max-w-3xl p-4">
        <Link
            :href="adminBlockRoutes.index.url({ lesson: lesson.id })"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Blocks
        </Link>

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Edit Block #{{ block.sort_order }}
                </h1>
                <p class="text-sm text-gray-500">
                    Lesson: {{ lesson.title }} · Type: {{ form.type }}
                </p>
            </div>

            <Button as-child variant="outline" size="sm">
                <Link :href="lessonRoutes.show.url(lesson.slug)">
                    <Eye class="h-3.5 w-3.5" />
                    Preview as Student
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Konten Block</CardTitle>
            </CardHeader>

            <CardContent>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <Label for="sort_order">Sort Order</Label>
                        <Input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            required
                        />
                    </div>

                    <BlockEditorDispatcher
                        v-model="form.content"
                        :type="form.type"
                    />

                    <p v-if="errors.content" class="text-xs text-red-600">
                        {{ errors.content }}
                    </p>

                    <div class="flex gap-2 pt-4">
                        <Button type="submit">Simpan</Button>

                        <Button as-child variant="outline">
                            <Link
                                :href="
                                    adminBlockRoutes.index.url({
                                        lesson: lesson.id,
                                    })
                                "
                            >
                                Batal
                            </Link>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
