<script setup lang="ts">
import { computed } from 'vue';
import CodeChallengeBlockEditor from './blockEditors/CodeChallengeBlockEditor.vue';
import CodeExampleBlockEditor from './blockEditors/CodeExampleBlockEditor.vue';
import CodeFillBlockEditor from './blockEditors/CodeFillBlockEditor.vue';
import CodeReorderBlockEditor from './blockEditors/CodeReorderBlockEditor.vue';
import HintBlockEditor from './blockEditors/HintBlockEditor.vue';
import McqMultipleBlockEditor from './blockEditors/McqMultipleBlockEditor.vue';
import McqSingleBlockEditor from './blockEditors/McqSingleBlockEditor.vue';
import TextBlockEditor from './blockEditors/TextBlockEditor.vue';

const model = defineModel<Record<string, unknown>>({ required: true });

const props = defineProps<{
    type: string;
}>();

const componentName = computed(() => {
    switch (props.type) {
        case 'TEXT':
            return TextBlockEditor;
        case 'CODE_EXAMPLE':
            return CodeExampleBlockEditor;
        case 'HINT':
            return HintBlockEditor;
        case 'MCQ_SINGLE':
            return McqSingleBlockEditor;
        case 'MCQ_MULTIPLE':
            return McqMultipleBlockEditor;
        case 'CODE_FILL':
            return CodeFillBlockEditor;
        case 'CODE_REORDER':
            return CodeReorderBlockEditor;
        case 'CODE_CHALLENGE':
            return CodeChallengeBlockEditor;
        default:
            return null;
    }
});
</script>

<template>
    <component :is="componentName" v-if="componentName" v-model="model" />

    <p v-else class="text-sm text-gray-500">Tipe block belum didukung.</p>
</template>
