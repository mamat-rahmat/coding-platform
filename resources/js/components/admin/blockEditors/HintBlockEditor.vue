<script setup lang="ts">
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

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Isi (Markdown)
            </label>
            <textarea
                v-model="model.text"
                class="w-full rounded-md border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
                rows="5"
            />
        </div>
    </div>
</template>
