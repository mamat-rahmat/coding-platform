<script setup lang="ts">
defineProps<{
    content: {
        language?: string;
        code?: string;
    };
}>();
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-gray-900">
        <div
            class="border-b border-gray-700 px-4 py-2 text-xs text-gray-400"
        >
            {{ content.language }}
        </div>

        <pre
            class="overflow-x-auto p-5 text-sm leading-6 text-gray-100"
        ><code>{{ content.code }}</code></pre>
    </div>
</template>