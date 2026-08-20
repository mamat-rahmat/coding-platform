<script setup lang="ts">
import CodeEditor from '@/components/CodeEditor.vue';

interface CodeExampleContent {
    language: string;
    code: string;
    markdown?: string;
}

const model = defineModel<CodeExampleContent>({ required: true });

if (typeof model.value.language !== 'string') {
    model.value.language = 'python';
}

if (typeof model.value.code !== 'string') {
    model.value.code = '';
}

if (typeof model.value.markdown !== 'string') {
    model.value.markdown = '';
}
</script>

<template>
    <div class="space-y-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Judul / Penjelasan (Markdown)
            </label>
            <textarea
                v-model="model.markdown"
                class="w-full rounded-md border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
                rows="5"
                placeholder="Apa yang dicontohkan kode ini?"
            />
        </div>

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
