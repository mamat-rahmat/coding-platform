<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface QuizOption {
    id: string;
    text: string;
}

interface McqMultipleContent {
    question: string;
    code?: string;
    options: QuizOption[];
    correct_answers: string[];
}

const props = defineProps<{
    blockId: number;
    content: McqMultipleContent;
    isAnswered?: boolean;
    isCorrect?: boolean | null;
    selectedAnswer?: string | null;
}>();

const selectedAnswers = ref<string[]>(
    props.selectedAnswer ? props.selectedAnswer.split(',') : [],
);
const submitted = ref(props.isAnswered ?? false);
const isCorrect = ref<boolean | null>(props.isAnswered ? (props.isCorrect ?? null) : null);

const toggleAnswer = (optionId: string) => {
    if (submitted.value) {
        return;
    }

    if (selectedAnswers.value.includes(optionId)) {
        selectedAnswers.value = selectedAnswers.value.filter(
            (id) => id !== optionId,
        );
    } else {
        selectedAnswers.value.push(optionId);
    }
};

const canSubmit = computed(() => selectedAnswers.value.length > 0);

const sortedSelected = computed(() => [...selectedAnswers.value].sort());

const isOptionCorrect = (optionId: string) =>
    props.content.correct_answers.includes(optionId);

const isOptionSelected = (optionId: string) =>
    selectedAnswers.value.includes(optionId);

const submitAnswer = () => {
    if (!canSubmit.value) {
        return;
    }

    router.post(
        attemptRoutes.store.url(props.blockId),
        {
            answer: sortedSelected.value.join(','),
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
    if (props.isAnswered) {
        return;
    }

    selectedAnswers.value = [];
    submitted.value = false;
    isCorrect.value = null;
};
</script>

<template>
    <div>
        <h3 class="text-lg font-semibold text-gray-900">
            {{ content.question }}
        </h3>

        <p class="mt-1 text-xs text-gray-500">
            Pilih semua jawaban yang benar.
        </p>

        <div
            v-if="content.code"
            class="mt-4 overflow-hidden rounded-lg bg-gray-900"
        >
            <pre
                class="overflow-x-auto p-5 text-sm leading-6 text-gray-100"
            ><code>{{ content.code }}</code></pre>
        </div>

        <div class="mt-5 space-y-3">
            <button
                v-for="option in content.options"
                :key="option.id"
                type="button"
                class="flex w-full items-center gap-3 rounded-lg border border-gray-300 bg-white p-4 text-left text-gray-900 transition hover:bg-gray-50"
                :class="{
                    'border-gray-900 bg-gray-100':
                        isOptionSelected(option.id) && !submitted,

                    'border-green-500 bg-green-50 text-green-900':
                        submitted && isCorrect && isOptionCorrect(option.id),

                    'border-red-500 bg-red-50 text-red-900':
                        submitted &&
                        isOptionSelected(option.id) &&
                        !isOptionCorrect(option.id),
                }"
                :disabled="submitted"
                @click="toggleAnswer(option.id)"
            >
                <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-300 text-sm font-medium text-gray-700"
                    :class="{
                        'border-gray-900 bg-gray-900 text-white':
                            isOptionSelected(option.id) && !submitted,
                        'border-green-500 bg-green-500 text-white':
                            submitted &&
                            isCorrect &&
                            isOptionCorrect(option.id),
                        'border-red-500 bg-red-500 text-white':
                            submitted &&
                            isOptionSelected(option.id) &&
                            !isOptionCorrect(option.id),
                    }"
                >
                    {{ option.id.toUpperCase() }}
                </span>

                <span>{{ option.text }}</span>
            </button>
        </div>

        <div class="mt-5">
            <button
                v-if="!submitted"
                type="button"
                :disabled="!canSubmit"
                class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-40"
                @click="submitAnswer"
            >
                Jawab
            </button>

            <button
                v-else-if="!isAnswered"
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
                {{ isCorrect ? '✓ Jawaban benar!' : '✗ Jawaban belum benar.' }}
            </p>

            <p v-if="!isCorrect" class="mt-1 text-sm">
                Coba perhatikan kembali soal dan pilih jawaban yang tepat.
            </p>
        </div>
    </div>
</template>
