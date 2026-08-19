<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import adminRoutes from '@/routes/admin';
import adminCourseRoutes from '@/routes/admin/courses';
import adminModuleRoutes from '@/routes/admin/modules';

interface Course {
    id: number;
    title: string;
}

interface Module {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    sort_order: number;
}

const props = defineProps<{
    course: Course;
    module: Module;
}>();

const form = reactive({
    title: props.module.title,
    slug: props.module.slug,
    description: props.module.description ?? '',
    sort_order: props.module.sort_order,
});

const errors = reactive<Record<string, string>>({});

function submit() {
    router.put(adminModuleRoutes.update.url(props.module.id), form, {
        onError: (serverErrors) => {
            Object.assign(errors, serverErrors);
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            {
                title: 'Courses',
                href: adminCourseRoutes.index.url(),
            },
            { title: 'Modules', href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Edit Module" />

    <div class="mx-auto max-w-2xl p-4">
        <Link
            :href="adminModuleRoutes.show.url(module.id)"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Module
        </Link>

        <h1 class="mb-6 text-2xl font-bold tracking-tight">Edit Module</h1>

        <Card>
            <CardHeader>
                <CardTitle>{{ module.title }}</CardTitle>
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

                    <div class="flex gap-2 pt-4">
                        <Button type="submit">Update</Button>

                        <Button as-child variant="outline">
                            <Link :href="adminModuleRoutes.show.url(module.id)">
                                Batal
                            </Link>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
