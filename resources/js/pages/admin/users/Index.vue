<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, ShieldCheck } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import adminRoutes from '@/routes/admin';
import adminUserRoutes from '@/routes/admin/users';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    email_verified_at: string | null;
    created_at: string;
    lesson_progresses_count: number;
    block_attempts_count: number;
}

const props = defineProps<{
    users: User[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Users', href: adminUserRoutes.index.url() },
        ],
    },
});

const search = ref('');

const filteredUsers = computed(() => {
    if (!search.value) {
        return props.users;
    }

    const q = search.value.toLowerCase();

    return props.users.filter(
        (user) =>
            user.name.toLowerCase().includes(q) ||
            user.email.toLowerCase().includes(q),
    );
});

function destroy(user: User) {
    if (!confirm(`Hapus user "${user.name}"?`)) {
        return;
    }

    router.delete(adminUserRoutes.destroy.url(user.id));
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Admin - Users" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Users</h1>
                <p class="text-sm text-gray-500">
                    Kelola semua pengguna.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <Input
                v-model="search"
                placeholder="Cari nama atau email..."
                class="max-w-sm"
            />
            <span class="text-sm text-gray-500">
                {{ filteredUsers.length }} user
            </span>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Daftar User</CardTitle>
            </CardHeader>

            <CardContent>
                <div
                    v-if="filteredUsers.length === 0"
                    class="py-8 text-center text-sm text-gray-500"
                >
                    Tidak ada user ditemukan.
                </div>

                <div v-else class="divide-y">
                    <div
                        v-for="user in filteredUsers"
                        :key="user.id"
                        class="flex items-center justify-between py-3"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900">
                                    {{ user.name }}
                                </span>

                                <span
                                    v-if="user.is_admin"
                                    class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700"
                                >
                                    <ShieldCheck class="h-3 w-3" />
                                    Admin
                                </span>

                                <span
                                    v-if="!user.email_verified_at"
                                    class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700"
                                >
                                    Unverified
                                </span>
                            </div>

                            <p class="text-xs text-gray-500">
                                {{ user.email }} · {{
                                    user.lesson_progresses_count
                                }} lessons completed · {{
                                    user.block_attempts_count
                                }} attempts · Joined
                                {{ formatDate(user.created_at) }}
                            </p>
                        </div>

                        <div class="flex gap-1">
                            <Button as-child variant="ghost" size="icon-sm">
                                <Link
                                    :href="
                                        adminUserRoutes.edit.url(user.id)
                                    "
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon-sm"
                                :disabled="user.is_admin"
                                @click="destroy(user)"
                            >
                                <Trash2
                                    class="h-4 w-4"
                                    :class="
                                        user.is_admin
                                            ? 'text-gray-300'
                                            : 'text-red-600'
                                    "
                                />
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
