<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface QuizOption {
    id: string;
    text: string;
}

interface QuizContent {
    question: string;
    code?: string;
    options: QuizOption[];
}

const props = defineProps<{
    blockId: number;
    content: QuizContent;
}>();

const selectedAnswer = ref<string | null>(null);
const submitted = ref(false);

const isCorrect = ref<boolean | null>(null);

const submitAnswer = () => {
    if (!selectedAnswer.value) {
        return;
    }

    router.post(
        `/lesson-blocks/${props.blockId}/quiz`,
        {
            answer: selectedAnswer.value,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash as {
                    quiz_result?: {
                        block_id: number;
                        selected_answer: string;
                        is_correct: boolean;
                    };
                };

                const result = flash.quiz_result;

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
    selectedAnswer.value = null;
    submitted.value = false;
};
</script>

<template>
    <div>
        <h3 class="text-lg font-semibold text-gray-900">
            {{ content.question }}
        </h3>

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
                        selectedAnswer === option.id && !submitted,

                    'border-green-500 bg-green-50 text-green-900':
                        submitted &&
                        option.id === content.correct_answer,

                    'border-red-500 bg-red-50 text-red-900':
                        submitted &&
                        option.id === selectedAnswer &&
                        option.id !== content.correct_answer,
                }"
                :disabled="submitted"
                @click="selectedAnswer = option.id"
            >
            <span
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-gray-300 text-sm font-medium text-gray-700"
            >
                {{ option.id.toUpperCase() }}
            </span>

                <span>
                    {{ option.text }}
                </span>
            </button>
        </div>

        <div class="mt-5">
            <button
                v-if="!submitted"
                type="button"
                :disabled="!selectedAnswer"
                class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-40"
                @click="submitAnswer"
            >
                Jawab
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
                {{ isCorrect ? '✓ Jawaban benar!' : '✗ Jawaban belum benar.' }}
            </p>

            <p
                v-if="!isCorrect"
                class="mt-1 text-sm"
            >
                Coba perhatikan kembali soal dan pilih jawaban yang tepat.
            </p>
        </div>
    </div>
</template>