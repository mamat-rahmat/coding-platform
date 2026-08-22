<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import adminRoutes from '@/routes/admin';
import adminUserRoutes from '@/routes/admin/users';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    lesson_progresses_count: number;
    block_attempts_count: number;
}

const props = defineProps<{
    user: User;
}>();

const form = reactive({
    name: props.user.name,
    email: props.user.email,
    is_admin: props.user.is_admin,
    password: '',
    password_confirmation: '',
});

const errors = reactive<Record<string, string>>({});

function submit() {
    router.put(adminUserRoutes.update.url(props.user.id), form, {
        onError: (serverErrors) => {
            Object.assign(errors, serverErrors);
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Users', href: adminUserRoutes.index.url() },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Edit User" />

    <div class="mx-auto max-w-2xl p-4">
        <Link
            :href="adminUserRoutes.index.url()"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Users
        </Link>

        <h1 class="mb-6 text-2xl font-bold tracking-tight">Edit User</h1>

        <Card>
            <CardHeader>
                <CardTitle>{{ user.name }}</CardTitle>
            </CardHeader>

            <CardContent>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" required />
                        <p
                            v-if="errors.name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                        />
                        <p
                            v-if="errors.email"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ errors.email }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="is_admin"
                            v-model="form.is_admin"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300"
                        />
                        <Label for="is_admin">Admin</Label>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="mb-3 text-sm font-medium text-gray-700">
                            Ubah Password
                        </p>
                        <p class="mb-3 text-xs text-gray-500">
                            Kosongkan jika tidak ingin mengubah password.
                        </p>

                        <div class="space-y-3">
                            <div>
                                <Label for="password">Password Baru</Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    autocomplete="new-password"
                                />
                                <p
                                    v-if="errors.password"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ errors.password }}
                                </p>
                            </div>

                            <div>
                                <Label for="password_confirmation">
                                    Konfirmasi Password
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <Button type="submit">Update</Button>

                        <Button as-child variant="outline">
                            <Link :href="adminUserRoutes.index.url()">
                                Batal
                            </Link>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
