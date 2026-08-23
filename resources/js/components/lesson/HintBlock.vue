<script setup lang="ts">
import { router } from '@inertiajs/vue3';
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

        router.post(
            attemptRoutes.store.url(props.blockId),
            { answer: '' },
            { preserveScroll: true, only: [] },
        );
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
