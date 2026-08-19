<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface Blank {
    id: string;
    answer: string;
    alternatives?: string[];
}

interface CodeFillContent {
    code_template: string;
    blanks: Blank[];
}

const props = defineProps<{
    blockId: number;
    content: CodeFillContent;
}>();

const blankInputs = ref<Record<string, string>>(
    Object.fromEntries(props.content.blanks.map((b) => [b.id, ''])),
);
const submitted = ref(false);
const isCorrect = ref<boolean | null>(null);
const blankResults = ref<Record<string, boolean>>({});

const filledBlanks = computed(
    () =>
        Object.values(blankInputs.value).filter((v) => v.trim() !== '').length,
);

const allBlanksFilled = computed(
    () => filledBlanks.value === props.content.blanks.length,
);

const renderedCode = computed(() => {
    let code = props.content.code_template;

    for (const blank of props.content.blanks) {
        const value = blankInputs.value[blank.id] || '_____';
        const isWrong = submitted.value && !blankResults.value[blank.id];
        const display = isWrong
            ? `${value} (✗)`
            : submitted.value && blankResults.value[blank.id]
              ? `${value} (✓)`
              : value;

        code = code.replace(`{{${blank.id}}}`, display);
    }

    return code;
});

const checkBlank = (blank: Blank, value: string): boolean => {
    const trimmed = value.trim();
    const correct = [blank.answer, ...(blank.alternatives ?? [])].map((v) =>
        v.trim(),
    );

    return correct.includes(trimmed);
};

const submitAnswer = () => {
    if (!allBlanksFilled.value) {
        return;
    }

    const results: Record<string, boolean> = {};

    for (const blank of props.content.blanks) {
        results[blank.id] = checkBlank(blank, blankInputs.value[blank.id]);
    }

    const correctCount = Object.values(results).filter(Boolean).length;
    const allCorrect = correctCount === props.content.blanks.length;

    const answersPayload = props.content.blanks
        .map((b) => `${b.id}:${blankInputs.value[b.id]}`)
        .join('|');

    router.post(
        attemptRoutes.store.url(props.blockId),
        {
            answer: answersPayload,
            is_correct: allCorrect,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash as {
                    attempt_result?: {
                        block_id: number;
                        selected_answer: string;
                        is_correct: boolean;
                    };
                };

                const result = flash.attempt_result;

                if (!result || result.block_id !== props.blockId) {
                    return;
                }

                submitted.value = true;
                isCorrect.value = result.is_correct;
                blankResults.value = results;
            },
        },
    );
};

const reset = () => {
    for (const blank of props.content.blanks) {
        blankInputs.value[blank.id] = '';
    }

    submitted.value = false;
    isCorrect.value = null;
    blankResults.value = {};
};
</script>

<template>
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Lengkapi Kode</h3>

        <p class="mt-1 text-xs text-gray-500">
            Isi bagian yang kosong untuk melengkapi kode.
        </p>

        <div class="mt-4 overflow-hidden rounded-lg bg-gray-900 p-5">
            <pre
                class="overflow-x-auto text-sm leading-6 text-gray-100"
            ><code>{{ renderedCode }}</code></pre>
        </div>

        <div class="mt-5 space-y-4">
            <div v-for="blank in content.blanks" :key="blank.id">
                <label
                    :for="`blank-${blank.id}`"
                    class="mb-1 block text-sm font-medium text-gray-700"
                >
                    Isian {{ blank.id.toUpperCase() }}
                </label>

                <Input
                    :id="`blank-${blank.id}`"
                    v-model="blankInputs[blank.id]"
                    :disabled="submitted"
                    placeholder="Ketik jawaban..."
                    class="max-w-xs"
                    :class="{
                        'border-green-500 bg-green-50':
                            submitted && blankResults[blank.id],
                        'border-red-500 bg-red-50':
                            submitted && !blankResults[blank.id],
                    }"
                />
            </div>
        </div>

        <div class="mt-5">
            <button
                v-if="!submitted"
                type="button"
                :disabled="!allBlanksFilled"
                class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-40"
                @click="submitAnswer"
            >
                Periksa Jawaban
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
                {{
                    isCorrect
                        ? '✓ Semua jawaban benar!'
                        : '✗ Beberapa jawaban belum benar.'
                }}
            </p>

            <p v-if="!isCorrect" class="mt-1 text-sm">
                Periksa kembali isian yang ditandai merah.
            </p>
        </div>
    </div>
</template>
