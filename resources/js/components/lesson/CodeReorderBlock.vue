<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { GripVertical } from '@lucide/vue';
import { computed, ref } from 'vue';
import draggable from 'vuedraggable';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface CodeReorderContent {
    lines: string[];
    correct_order: number[];
}

const props = defineProps<{
    blockId: number;
    content: CodeReorderContent;
}>();

const shuffledIndices = ref<number[]>(
    (() => {
        const indices = props.content.lines.map((_, i) => i);

        for (let i = indices.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [indices[i], indices[j]] = [indices[j], indices[i]];
        }

        const isAlreadyCorrect = indices.every(
            (val, idx) => val === props.content.correct_order[idx],
        );

        if (isAlreadyCorrect && indices.length > 1) {
            [indices[0], indices[1]] = [indices[1], indices[0]];
        }

        return indices;
    })(),
);

const submitted = ref(false);
const isCorrect = ref<boolean | null>(null);

const currentOrder = computed(() => [...shuffledIndices.value]);

const isLineCorrect = (position: number) => {
    if (!submitted.value) {
        return null;
    }

    return (
        currentOrder.value[position] === props.content.correct_order[position]
    );
};

const submitAnswer = () => {
    const orderString = currentOrder.value.join(',');

    router.post(
        attemptRoutes.store.url(props.blockId),
        {
            answer: orderString,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const result = (
                    page.props.flash as
                        | {
                              attempt_result?: {
                                  block_id: number;
                                  selected_answer: string;
                                  is_correct: boolean;
                              };
                          }
                        | undefined
                )?.attempt_result;

                if (!result || result.block_id !== props.blockId) {
                    return;
                }

                submitted.value = true;
                isCorrect.value = result.is_correct;
            },
        },
    );
};

const reset = () => {
    const indices = props.content.lines.map((_, i) => i);

    for (let i = indices.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [indices[i], indices[j]] = [indices[j], indices[i]];
    }

    shuffledIndices.value = indices;
    submitted.value = false;
    isCorrect.value = null;
};
</script>

<template>
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Urutkan Kode</h3>

        <p class="mt-1 text-xs text-gray-500">
            Seret baris kode untuk menyusun urutan yang benar.
        </p>

        <draggable
            v-model="shuffledIndices"
            :item-key="(item: number) => String(item)"
            :disabled="submitted"
            handle=".drag-handle"
            class="mt-4 space-y-2"
        >
            <template #item="{ element, index }">
                <div
                    class="flex items-center gap-3 rounded-lg border bg-white p-3"
                    :class="{
                        'border-green-500 bg-green-50':
                            isLineCorrect(index) === true,
                        'border-red-500 bg-red-50':
                            isLineCorrect(index) === false,
                        'border-gray-300': isLineCorrect(index) === null,
                    }"
                >
                    <span
                        class="drag-handle flex h-6 w-6 shrink-0 cursor-move items-center justify-center text-gray-400"
                    >
                        <GripVertical class="h-4 w-4" />
                    </span>

                    <span class="font-mono text-xs text-gray-500">
                        {{ index + 1 }}.
                    </span>

                    <pre
                        class="overflow-x-auto font-mono text-sm text-gray-900"
                    ><code>{{ content.lines[element] }}</code></pre>
                </div>
            </template>
        </draggable>

        <div class="mt-5">
            <button
                v-if="!submitted"
                type="button"
                class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white"
                @click="submitAnswer"
            >
                Periksa Urutan
            </button>

            <button
                v-else
                type="button"
                class="rounded-lg border px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="reset"
            >
                Coba lagi
            </button>
        </div>

        <div
            v-if="submitted"
            class="mt-5 rounded-lg p-4"
            :class="
                isCorrect
                    ? 'bg-green-50 text-green-700'
                    : 'bg-red-50 text-red-700'
            "
        >
            <p class="font-medium">
                {{ isCorrect ? '✓ Urutan benar!' : '✗ Urutan belum benar.' }}
            </p>

            <p v-if="!isCorrect" class="mt-1 text-sm">
                Baris yang salah ditandai merah. Coba susun ulang.
            </p>
        </div>
    </div>
</template>
