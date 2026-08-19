<script setup lang="ts">
import CodeEditor from '@/components/CodeEditor.vue';

interface CodeExampleContent {
    language: string;
    code: string;
}

defineProps<{
    content: CodeExampleContent;
}>();
</script>

<template>
    <div class="overflow-hidden rounded-lg">
        <div
            class="flex items-center justify-between border-b border-gray-700 bg-gray-900 px-4 py-2 text-xs text-gray-400"
        >
            <span>{{ content.language }}</span>
            <span class="tracking-wide uppercase">Contoh</span>
        </div>

        <CodeEditor
            :model-value="content.code"
            :language="content.language"
            readonly
        />
    </div>
</template>
