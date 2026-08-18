<script setup lang="ts">
import TextBlock from './TextBlock.vue';
import CodeExampleBlock from './CodeExampleBlock.vue';

interface LessonBlock {
    id: number;
    type: string;
    content: {
        text?: string;
        language?: string;
        code?: string;
    };
    sort_order: number;
}

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

    <p
        v-else
        class="text-sm text-gray-500"
    >
        Block type "{{ block.type }}" belum didukung.
    </p>
</template>