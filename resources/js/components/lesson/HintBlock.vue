<script setup lang="ts">
import { Lightbulb } from '@lucide/vue';
import { ref, watch } from 'vue';
import Markdown from '@/components/Markdown.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface HintContent {
    title: string;
    text: string | null;
}

const props = defineProps<{
    blockId: number;
    content: HintContent;
}>();

const isOpen = ref(false);
const tracked = ref(false);

watch(isOpen, (open) => {
    if (open && !tracked.value) {
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
    }
});
</script>

<template>
    <Collapsible
        v-model:open="isOpen"
        class="rounded-lg border border-amber-200 bg-amber-50"
    >
        <CollapsibleTrigger
            class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-medium text-amber-900 hover:bg-amber-100"
        >
            <Lightbulb class="h-4 w-4 shrink-0" />
            <span>{{ content.title }}</span>
            <span class="ml-auto text-xs font-normal text-amber-700">
                {{ isOpen ? 'Tutup' : 'Lihat petunjuk' }}
            </span>
        </CollapsibleTrigger>

        <CollapsibleContent class="border-t border-amber-200 px-4 py-3">
            <Markdown :content="content.text" class="text-sm text-amber-900" />
        </CollapsibleContent>
    </Collapsible>
</template>
