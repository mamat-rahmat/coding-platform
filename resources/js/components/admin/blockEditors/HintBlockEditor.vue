<script setup lang="ts">
import Markdown from '@/components/Markdown.vue';

interface HintContent {
    title: string;
    text: string;
}

const model = defineModel<HintContent>({ required: true });

if (typeof model.value.title !== 'string') {
    model.value.title = 'Petunjuk';
}

if (typeof model.value.text !== 'string') {
    model.value.text = '';
}
</script>

<template>
    <div class="space-y-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Judul Hint
            </label>
            <input
                v-model="model.title"
                class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
            />
        </div>

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Isi (Markdown)
                </label>
                <textarea
                    v-model="model.text"
                    class="h-48 w-full rounded-md border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
                    rows="5"
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
                        v-if="(model.text || '').trim() === ''"
                        class="text-sm text-gray-400"
                    >
                        Preview akan tampil di sini.
                    </p>
                    <Markdown v-else :content="model.text" />
                </div>
            </div>
        </div>
    </div>
</template>
