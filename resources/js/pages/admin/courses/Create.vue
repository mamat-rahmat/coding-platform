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

const form = reactive({
    title: '',
    slug: '',
    description: '',
    language: 'python',
    level: 'beginner',
    thumbnail: '',
    xp_reward: 100,
    is_published: false,
});

const errors = reactive<Record<string, string>>({});

function submit() {
    router.post(adminCourseRoutes.store.url(), form, {
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
            { title: 'Create', href: adminCourseRoutes.create.url() },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Course Baru" />

    <div class="mx-auto max-w-2xl p-4">
        <Link
            :href="adminCourseRoutes.index.url()"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Courses
        </Link>

        <h1 class="mb-6 text-2xl font-bold tracking-tight">Course Baru</h1>

        <Card>
            <CardHeader>
                <CardTitle>Detail Course</CardTitle>
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
                        <Input
                            id="slug"
                            v-model="form.slug"
                            required
                            placeholder="mis: python-fundamentals"
                        />
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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="language">Language</Label>
                            <Input
                                id="language"
                                v-model="form.language"
                                required
                            />
                        </div>

                        <div>
                            <Label for="level">Level</Label>
                            <select
                                id="level"
                                v-model="form.level"
                                class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">
                                    Intermediate
                                </option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <Label for="xp_reward">XP Reward</Label>
                        <Input
                            id="xp_reward"
                            v-model.number="form.xp_reward"
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
                        <Button type="submit">Simpan</Button>

                        <Button as-child variant="outline">
                            <Link :href="adminCourseRoutes.index.url()">
                                Batal
                            </Link>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
