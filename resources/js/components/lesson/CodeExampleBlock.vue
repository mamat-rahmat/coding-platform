<script setup lang="ts">
interface CodeExampleContent {
    language: string;
    code: string;
}

defineProps<{
    content: CodeExampleContent;
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