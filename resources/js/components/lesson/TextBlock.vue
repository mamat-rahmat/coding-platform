<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Markdown from '@/components/Markdown.vue';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface TextContent {
    text: string;
}

const props = defineProps<{
    blockId: number;
    content: TextContent;
}>();

const tracked = ref(false);

onMounted(() => {
    if (tracked.value) {
        return;
    }

    tracked.value = true;

    fetch(attemptRoutes.store.url(props.blockId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': decodeURIComponent(
                document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
            ),
        },
        body: JSON.stringify({ answer: '' }),
    });
});
</script>

<template>
    <Markdown
        :content="content.text"
        class="prose prose-sm max-w-none text-gray-700"
    />
</template>
