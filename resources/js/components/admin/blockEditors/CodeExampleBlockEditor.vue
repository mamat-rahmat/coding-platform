<script setup lang="ts">
import CodeEditor from '@/components/CodeEditor.vue';
import Markdown from '@/components/Markdown.vue';

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
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Judul / Penjelasan (Markdown)
                </label>
                <textarea
                    v-model="model.markdown"
                    class="h-48 w-full rounded-md border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
                    rows="5"
                    placeholder="Apa yang dicontohkan kode ini?"
                />
            </div>

            <div class="flex flex-col">
                <span class="mb-1 block text-sm font-medium text-gray-700">
                    Preview
                </span>
                <div
                    class="h-48 flex-1 overflow-y-auto rounded-md border border-dashed border-gray-300 bg-gray-50 p-3"
                >
                    <p
                        v-if="(model.markdown || '').trim() === ''"
                        class="text-sm text-gray-400"
                    >
                        Preview akan tampil di sini.
                    </p>
                    <Markdown v-else :content="model.markdown ?? ''" />
                </div>
            </div>
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
