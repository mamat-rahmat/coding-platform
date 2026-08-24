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

const props = defineProps<{
    block: LessonBlock;
}>();

const blockTypeLabels: Record<string, string> = {
    TEXT: 'Materi',
    CODE_EXAMPLE: 'Contoh Kode',
    HINT: 'Hint',
    MCQ_SINGLE: 'Pilihan Ganda',
    MCQ_MULTIPLE: 'Pilihan Ganda (Multi)',
    CODE_FILL: 'Lengkapi Kode',
    CODE_REORDER: 'Susun Kode',
    CODE_CHALLENGE: 'Tantangan Kode',
};

const blockAnswered = (props.block as LessonBlock & { is_answered?: boolean }).is_answered ?? false;
const blockCorrect = (props.block as LessonBlock & { is_correct?: boolean }).is_correct ?? null;
const blockSelectedAnswer = (props.block as LessonBlock & { selected_answer?: string }).selected_answer ?? null;
const blockAttemptData = (props.block as LessonBlock & { attempt_data?: Record<string, unknown> | null }).attempt_data ?? null;
</script>

<template>
    <div>
        <h2
            v-if="block.title"
            class="mb-2 text-sm font-medium text-gray-500"
        >
            [{{ blockTypeLabels[block.type] ?? block.type }}] {{ block.title }}
        </h2>

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
            :is-answered="blockAnswered"
            :is-correct="blockCorrect"
            :selected-answer="blockSelectedAnswer"
        />

        <McqMultipleBlock
            v-else-if="block.type === 'MCQ_MULTIPLE'"
            :block-id="block.id"
            :content="block.content"
            :is-answered="blockAnswered"
            :is-correct="blockCorrect"
            :selected-answer="blockSelectedAnswer"
        />

        <CodeFillBlock
            v-else-if="block.type === 'CODE_FILL'"
            :block-id="block.id"
            :content="block.content"
            :is-answered="blockAnswered"
            :is-correct="blockCorrect"
            :selected-answer="blockSelectedAnswer"
        />

        <CodeReorderBlock
            v-else-if="block.type === 'CODE_REORDER'"
            :block-id="block.id"
            :content="block.content"
            :is-answered="blockAnswered"
            :is-correct="blockCorrect"
            :selected-answer="blockSelectedAnswer"
        />

        <CodeChallengeBlock
            v-else-if="block.type === 'CODE_CHALLENGE'"
            :block-id="block.id"
            :content="block.content"
            :is-answered="blockAnswered"
            :is-correct="blockCorrect"
            :selected-answer="blockSelectedAnswer"
            :attempt-data="blockAttemptData"
        />

        <p v-else class="text-sm text-gray-500">Block type belum didukung.</p>
    </div>
</template>
