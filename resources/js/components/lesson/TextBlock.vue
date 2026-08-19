<script setup lang="ts">
import Markdown from '@/components/Markdown.vue';

interface TextContent {
    text: string;
}

defineProps<{
    content: TextContent;
}>();
</script>

<template>
    <Markdown
        :content="content.text"
        class="prose prose-sm max-w-none text-gray-700"
    />
</template>
