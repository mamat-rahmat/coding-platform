<script setup lang="ts">
import TextBlock from './TextBlock.vue';
import CodeExampleBlock from './CodeExampleBlock.vue';
import QuizBlock from './QuizBlock.vue';

interface BaseLessonBlock {
    id: number;
    sort_order: number;
}

interface TextBlock extends BaseLessonBlock {
    type: 'TEXT';
    content: {
        text: string;
    };
}

interface CodeExampleBlock extends BaseLessonBlock {
    type: 'CODE_EXAMPLE';
    content: {
        language: string;
        code: string;
    };
}

interface QuizBlock extends BaseLessonBlock {
    type: 'QUIZ';
    content: {
        question: string;
        code?: string;
        options: {
            id: string;
            text: string;
        }[];
        correct_answer: string;
    };
}

type LessonBlock =
    | TextBlock
    | CodeExampleBlock
    | QuizBlock;

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