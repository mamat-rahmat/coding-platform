<script setup lang="ts">
interface TextContent {
    text: string;
}

const model = defineModel<TextContent>({ required: true });

if (typeof model.value.text !== 'string') {
    model.value.text = '';
}
</script>

<template>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
            Konten (Markdown)
        </label>
        <textarea
            v-model="model.text"
            class="w-full rounded-md border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
            rows="10"
            placeholder="# Judul&#10;&#10;Tulis materi di sini dengan **markdown**..."
        />
    </div>
</template>
