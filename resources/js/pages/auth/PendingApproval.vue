<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Clock } from '@lucide/vue';

defineOptions({
    layout: {
        title: 'Menunggu Persetujuan',
        description: 'Akun Anda sedang menunggu persetujuan dari admin.',
    },
});
</script>

<template>
    <Head title="Menunggu Persetujuan" />

    <div class="flex flex-col items-center gap-6 text-center">
        <div
            class="flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100"
        >
            <Clock class="h-8 w-8 text-yellow-600" />
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                Akun Menunggu Persetujuan
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Akun Anda telah berhasil dibuat dan sedang menunggu
                persetujuan dari admin. Anda akan dapat mengakses platform
                setelah akun disetujui.
            </p>
        </div>

        <button
            type="button"
            class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800"
            @click="router.post('/logout')"
        >
            Keluar
        </button>
    </div>
</template>
