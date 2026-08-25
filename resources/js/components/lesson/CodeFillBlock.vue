<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { X } from '@lucide/vue';
import { computed, ref } from 'vue';
import Markdown from '@/components/Markdown.vue';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface Blank {
    id: string;
    answer: string;
    alternatives?: string[];
}

interface CodeFillContent {
    code_template: string;
    blanks: Blank[];
    options?: string[];
    markdown?: string;
}

interface Option {
    index: number;
    value: string;
}

interface TemplatePart {
    type: 'text' | 'blank';
    value: string;
    blankIndex: number;
}

const props = defineProps<{
    blockId: number;
    content: CodeFillContent;
    isAnswered?: boolean;
    isCorrect?: boolean | null;
    selectedAnswer?: string | null;
}>();

const submitted = ref(props.isAnswered ?? false);
const isCorrect = ref<boolean | null>(
    props.isAnswered ? (props.isCorrect ?? null) : null,
);
const blankResults = ref<boolean[]>([]);

const shuffledOptions = ref<Option[]>(
    (() => {
        const correctAnswers = props.content.blanks.map((b) => b.answer);
        const extras = (props.content.options ?? []).filter(
            (v) => v.trim() !== '',
        );
        const allValues = [...correctAnswers, ...extras];
        const opts: Option[] = allValues.map((value, i) => ({
            index: i,
            value,
        }));

        for (let i = opts.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [opts[i], opts[j]] = [opts[j], opts[i]];
        }

        return opts;
    })(),
);

function restoreFromAnswer(): (number | null)[] {
    if (!props.selectedAnswer || !props.isAnswered) {
        return props.content.blanks.map(() => null);
    }

    const answerMap = new Map<string, string>();

    for (const part of props.selectedAnswer.split('|')) {
        const sep = part.indexOf(':');

        if (sep === -1) {
            continue;
        }

        const id = part.slice(0, sep);
        const value = part.slice(sep + 1);

        if (id) {
            answerMap.set(id, value);
        }
    }

    const usedOptIndices = new Set<number>();
    const placements: (number | null)[] = props.content.blanks.map((blank) => {
        const value = answerMap.get(blank.id);

        if (value === undefined) {
            return null;
        }

        const optIdx = shuffledOptions.value.findIndex(
            (o, idx) => o.value === value && !usedOptIndices.has(idx),
        );

        if (optIdx >= 0) {
            usedOptIndices.add(optIdx);

            return shuffledOptions.value[optIdx].index;
        }

        return null;
    });

    return placements;
}

const optionPlacements = ref<(number | null)[]>(
    props.isAnswered
        ? restoreFromAnswer()
        : props.content.blanks.map(() => null),
);

if (props.isAnswered) {
    blankResults.value = props.content.blanks.map((_, i) => checkBlank(i));
}

const activeBlankIndex = computed(() => {
    const idx = optionPlacements.value.indexOf(null);

    return idx === -1 ? null : idx;
});

const allBlanksFilled = computed(() =>
    optionPlacements.value.every((p) => p !== null),
);

const usedOptionIndices = computed(() => {
    const used = new Set<number>();

    for (const p of optionPlacements.value) {
        if (p !== null) {
            used.add(p);
        }
    }

    return used;
});

const isOptionUsed = (optIndex: number) =>
    usedOptionIndices.value.has(optIndex);

const templateParts = computed<TemplatePart[]>(() => {
    const parts: TemplatePart[] = [];
    const regex = /\{\{\s*(\w+)\s*\}\}/g;
    let lastIndex = 0;
    let match: RegExpExecArray | null;
    let blankIdx = 0;

    while ((match = regex.exec(props.content.code_template)) !== null) {
        if (match.index > lastIndex) {
            parts.push({
                type: 'text',
                value: props.content.code_template.slice(
                    lastIndex,
                    match.index,
                ),
                blankIndex: -1,
            });
        }

        parts.push({ type: 'blank', value: match[1], blankIndex: blankIdx });
        lastIndex = regex.lastIndex;
        blankIdx++;
    }

    if (lastIndex < props.content.code_template.length) {
        parts.push({
            type: 'text',
            value: props.content.code_template.slice(lastIndex),
            blankIndex: -1,
        });
    }

    return parts;
});

function getBlankDisplayValue(blankIndex: number): string {
    const optIdx = optionPlacements.value[blankIndex];

    if (optIdx === null) {
        return '_____';
    }

    const opt = shuffledOptions.value.find((o) => o.index === optIdx);

    return opt?.value ?? '_____';
}

function isBlankActive(blankIndex: number): boolean {
    return activeBlankIndex.value === blankIndex && !submitted.value;
}

function isBlankFilled(blankIndex: number): boolean {
    return optionPlacements.value[blankIndex] !== null;
}

function selectOption(optIndex: number) {
    if (submitted.value || isOptionUsed(optIndex)) {
        return;
    }

    if (activeBlankIndex.value === null) {
        return;
    }

    optionPlacements.value[activeBlankIndex.value] = optIndex;
}

function clearBlank(blankIndex: number) {
    if (submitted.value) {
        return;
    }

    optionPlacements.value[blankIndex] = null;
}

function getBlankAnswer(blankIndex: number): string {
    return props.content.blanks[blankIndex]?.answer ?? '';
}

function checkBlank(blankIndex: number): boolean {
    const blank = props.content.blanks[blankIndex];

    if (!blank) {
        return false;
    }

    const placedValue = getBlankDisplayValue(blankIndex).trim();
    const correct = [blank.answer, ...(blank.alternatives ?? [])].map((v) =>
        v.trim(),
    );

    return correct.includes(placedValue);
}

function submitAnswer() {
    if (!allBlanksFilled.value) {
        return;
    }

    const results = props.content.blanks.map((_, i) => checkBlank(i));
    const allCorrect = results.every(Boolean);

    const answersPayload = props.content.blanks
        .map((b, i) => `${b.id}:${getBlankDisplayValue(i)}`)
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
                blankResults.value = results;
            },
        },
    );
}

function reset() {
    if (props.isAnswered && props.isCorrect) {
        return;
    }

    optionPlacements.value = props.content.blanks.map(() => null);
    submitted.value = false;
    isCorrect.value = null;
    blankResults.value = [];

    for (let i = shuffledOptions.value.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffledOptions.value[i], shuffledOptions.value[j]] = [
            shuffledOptions.value[j],
            shuffledOptions.value[i],
        ];
    }
}
</script>

<template>
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Lengkapi Kode</h3>

        <Markdown
            v-if="content.markdown"
            :content="content.markdown"
            class="mt-2"
        />

        <p class="mt-1 text-xs text-gray-500">
            Klik opsi di bawah untuk mengisi bagian kosong secara berurutan.
            Klik bagian yang terisi untuk menghapusnya.
        </p>

        <!-- Code with blanks -->
        <div class="mt-4 overflow-x-auto rounded-lg bg-gray-900 p-5">
            <pre
                class="text-sm leading-7 text-gray-100"
            ><code><template v-for="(part, i) in templateParts" :key="i"><span v-if="part.type === 'text'">{{ part.value }}</span><span
    v-else
    class="inline-flex items-center rounded mx-0.5 px-1.5 py-0.5 font-mono text-xs cursor-pointer transition"
    :class="{
        'border-2 border-dashed border-gray-500 text-gray-400': !isBlankFilled(part.blankIndex) && !submitted,
        'border-2 border-yellow-400 bg-yellow-400/20 text-yellow-200': isBlankActive(part.blankIndex),
        'border border-gray-600 bg-gray-700 text-gray-100': isBlankFilled(part.blankIndex) && !submitted,
        'border border-green-500 bg-green-500/20 text-green-200': submitted && blankResults[part.blankIndex],
        'border border-red-500 bg-red-500/20 text-red-200': submitted && !blankResults[part.blankIndex],
    }"
    @click="clearBlank(part.blankIndex)"
>{{ getBlankDisplayValue(part.blankIndex) }}</span></template></code></pre>
        </div>

        <!-- Available options -->
        <div v-if="!submitted" class="mt-5">
            <p class="mb-2 text-xs font-medium text-gray-600">Pilihan:</p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="opt in shuffledOptions"
                    :key="opt.index"
                    type="button"
                    :disabled="isOptionUsed(opt.index)"
                    class="rounded-lg border bg-white px-3 py-2 font-mono text-sm text-gray-900 transition enabled:hover:border-gray-900 enabled:hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30"
                    @click="selectOption(opt.index)"
                >
                    {{ opt.value }}
                </button>
            </div>
        </div>

        <!-- After submit: show correct answers for wrong blanks -->
        <div v-if="submitted && !isCorrect" class="mt-4">
            <p class="mb-2 text-xs font-medium text-gray-600">
                Jawaban yang benar:
            </p>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="(blank, i) in content.blanks"
                    :key="blank.id"
                    class="rounded border border-green-300 bg-green-50 px-2 py-1 font-mono text-xs text-green-800"
                >
                    {{ blank.id }}: {{ getBlankAnswer(i) }}
                </span>
            </div>
        </div>

        <!-- Action buttons -->
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
                v-else-if="!isCorrect"
                type="button"
                class="flex items-center gap-1.5 rounded-lg border px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="reset"
            >
                <X class="h-4 w-4" />
                Coba lagi
            </button>
        </div>

        <!-- Feedback -->
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
                Bagian yang salah ditandai merah. Lihat jawaban yang benar di
                atas.
            </p>
        </div>
    </div>
</template>
