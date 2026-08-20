<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import adminRoutes from '@/routes/admin';
import adminLessonRoutes from '@/routes/admin/lessons';

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
}

const props = defineProps<{
    lesson: Lesson;
}>();

const form = reactive({
    title: props.lesson.title,
    slug: props.lesson.slug,
    description: props.lesson.description ?? '',
    sort_order: props.lesson.sort_order,
    is_published: props.lesson.is_published,
});

const errors = reactive<Record<string, string>>({});

function submit() {
    router.put(adminLessonRoutes.update.url(props.lesson.id), form, {
        onError: (serverErrors) => {
            Object.assign(errors, serverErrors);
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Lessons', href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Edit Lesson" />

    <div class="p-4">
        <Link
            :href="adminLessonRoutes.show.url(lesson.id)"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Lesson
        </Link>

        <h1 class="mb-6 text-2xl font-bold tracking-tight">Edit Lesson</h1>

        <Card>
            <CardHeader>
                <CardTitle>{{ lesson.title }}</CardTitle>
            </CardHeader>

            <CardContent>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <Label for="title">Title</Label>
                        <Input id="title" v-model="form.title" required />
                    </div>

                    <div>
                        <Label for="slug">Slug</Label>
                        <Input id="slug" v-model="form.slug" required />
                    </div>

                    <div>
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
                            rows="3"
                        />
                    </div>

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

                    <div class="flex items-center gap-2">
                        <input
                            id="is_published"
                            v-model="form.is_published"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300"
                        />
                        <Label for="is_published">Published</Label>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <Button type="submit">Update</Button>

                        <Button as-child variant="outline">
                            <Link :href="adminLessonRoutes.show.url(lesson.id)">
                                Batal
                            </Link>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
