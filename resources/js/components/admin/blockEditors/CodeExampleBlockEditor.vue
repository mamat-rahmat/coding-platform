<script setup lang="ts">
import CodeEditor from '@/components/CodeEditor.vue';

interface CodeExampleContent {
    language: string;
    code: string;
}

const model = defineModel<CodeExampleContent>({ required: true });

if (typeof model.value.language !== 'string') {
    model.value.language = 'python';
}

if (typeof model.value.code !== 'string') {
    model.value.code = '';
}
</script>

<template>
    <div class="space-y-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Language
            </label>
            <input
                v-model="model.language"
                class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
                placeholder="python"
            />
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Code
            </label>
            <CodeEditor
                v-model="model.code"
                :language="model.language"
                autofocus
            />
        </div>
    </div>
</template>
