<script setup lang="ts">
import type { LessonBlock } from '@/types/lesson';
import CodeExampleBlock from './CodeExampleBlock.vue';
import McqSingleBlock from './McqSingleBlock.vue';
import TextBlock from './TextBlock.vue';

defineProps<{
    block: LessonBlock;
}>();
</script>

<template>
    <TextBlock v-if="block.type === 'TEXT'" :content="block.content" />

    <CodeExampleBlock
        v-else-if="block.type === 'CODE_EXAMPLE'"
        :content="block.content"
    />

    <McqSingleBlock
        v-else-if="block.type === 'MCQ_SINGLE'"
        :block-id="block.id"
        :content="block.content"
    />

    <p v-else class="text-sm text-gray-500">Block type belum didukung.</p>
</template>
