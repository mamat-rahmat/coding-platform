<script setup lang="ts">
import { python } from '@codemirror/lang-python';
import { oneDark } from '@codemirror/theme-one-dark';
import { computed } from 'vue';
import { Codemirror } from 'vue-codemirror';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        language?: string;
        readonly?: boolean;
        placeholder?: string;
        autofocus?: boolean;
    }>(),
    {
        language: 'python',
        readonly: false,
        placeholder: '',
        autofocus: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const extensions = computed(() => {
    const exts = [];

    if (props.language === 'python') {
        exts.push(python());
    }

    exts.push(oneDark);

    return exts;
});

const handleChange = (value: string) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <Codemirror
        :model-value="modelValue"
        :extensions="extensions"
        :disabled="readonly"
        :placeholder="placeholder"
        :autofocus="autofocus"
        :indent-with-tab="true"
        :tab-size="4"
        class="codemirror-wrapper"
        @update:model-value="handleChange"
    />
</template>

<style scoped>
.codemirror-wrapper :deep(.cm-editor) {
    border-radius: 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

.codemirror-wrapper :deep(.cm-scroller) {
    font-family:
        ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.codemirror-wrapper :deep(.cm-gutters) {
    border-top-left-radius: 0.5rem;
    border-bottom-left-radius: 0.5rem;
}
</style>
