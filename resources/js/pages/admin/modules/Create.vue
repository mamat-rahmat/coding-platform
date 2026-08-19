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
    slug: string;
}

const props = defineProps<{
    course: Course;
    nextSortOrder: number;
}>();

const form = reactive({
    title: '',
    slug: '',
    description: '',
    sort_order: props.nextSortOrder,
});

const errors = reactive<Record<string, string>>({});

function submit() {
    router.post(
        adminModuleRoutes.store.url({ course: props.course.id }),
        form,
        {
            onError: (serverErrors) => {
                Object.assign(errors, serverErrors);
            },
        },
    );
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
            { title: 'Create', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Module Baru" />

    <div class="mx-auto max-w-2xl p-4">
        <Link
            :href="adminModuleRoutes.index.url({ course: course.id })"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Modules
        </Link>

        <h1 class="mb-6 text-2xl font-bold tracking-tight">
            Module Baru — {{ course.title }}
        </h1>

        <Card>
            <CardHeader>
                <CardTitle>Detail Module</CardTitle>
            </CardHeader>

            <CardContent>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <Label for="title">Title</Label>
                        <Input id="title" v-model="form.title" required />
                        <p
                            v-if="errors.title"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ errors.title }}
                        </p>
                    </div>

                    <div>
                        <Label for="slug">Slug</Label>
                        <Input id="slug" v-model="form.slug" required />
                        <p v-if="errors.slug" class="mt-1 text-xs text-red-600">
                            {{ errors.slug }}
                        </p>
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
                        <Button type="submit">Simpan</Button>

                        <Button as-child variant="outline">
                            <Link
                                :href="
                                    adminModuleRoutes.index.url({
                                        course: course.id,
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
