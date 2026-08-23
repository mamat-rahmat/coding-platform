<script setup lang="ts">
import type { LessonBlock } from '@/types/lesson';
import CodeChallengeBlock from './CodeChallengeBlock.vue';
import CodeExampleBlock from './CodeExampleBlock.vue';
import CodeFillBlock from './CodeFillBlock.vue';
import CodeReorderBlock from './CodeReorderBlock.vue';
import HintBlock from './HintBlock.vue';
import McqMultipleBlock from './McqMultipleBlock.vue';
import McqSingleBlock from './McqSingleBlock.vue';
import TextBlock from './TextBlock.vue';

defineProps<{
    block: LessonBlock;
}>();
</script>

<template>
    <TextBlock v-if="block.type === 'TEXT'" :block-id="block.id" :content="block.content" />

    <CodeExampleBlock
        v-else-if="block.type === 'CODE_EXAMPLE'"
        :block-id="block.id"
        :content="block.content"
    />

    <HintBlock
        v-else-if="block.type === 'HINT'"
        :block-id="block.id"
        :content="block.content"
    />

    <McqSingleBlock
        v-else-if="block.type === 'MCQ_SINGLE'"
        :block-id="block.id"
        :content="block.content"
    />

    <McqMultipleBlock
        v-else-if="block.type === 'MCQ_MULTIPLE'"
        :block-id="block.id"
        :content="block.content"
    />

    <CodeFillBlock
        v-else-if="block.type === 'CODE_FILL'"
        :block-id="block.id"
        :content="block.content"
    />

    <CodeReorderBlock
        v-else-if="block.type === 'CODE_REORDER'"
        :block-id="block.id"
        :content="block.content"
    />

    <CodeChallengeBlock
        v-else-if="block.type === 'CODE_CHALLENGE'"
        :block-id="block.id"
        :content="block.content"
    />

    <p v-else class="text-sm text-gray-500">Block type belum didukung.</p>
</template>
