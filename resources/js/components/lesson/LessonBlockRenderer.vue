<script setup lang="ts">
import TextBlock from './TextBlock.vue';
import CodeExampleBlock from './CodeExampleBlock.vue';
import QuizBlock from './QuizBlock.vue';

import type { LessonBlock } from '@/types/lesson';

defineProps<{
    block: LessonBlock;
}>();
</script>

<template>
    <TextBlock
        v-if="block.type === 'TEXT'"
        :content="block.content"
    />

    <CodeExampleBlock
        v-else-if="block.type === 'CODE_EXAMPLE'"
        :content="block.content"
    />

    <QuizBlock
        v-else-if="block.type === 'QUIZ'"
        :content="block.content"
    />

    <p
        v-else
        class="text-sm text-gray-500"
    >
        Block type belum didukung.
    </p>
</template>