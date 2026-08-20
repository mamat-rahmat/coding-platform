<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Play, CheckCircle2, XCircle, Loader2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import CodeEditor from '@/components/CodeEditor.vue';
import Markdown from '@/components/Markdown.vue';
import { Button } from '@/components/ui/button';
import { usePyodide } from '@/composables/usePyodide';
import type { RunResult } from '@/composables/usePyodide';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface Testcase {
    id: string;
    input: string;
    expected_output: string;
    hidden: boolean;
}

interface CodeChallengeContent {
    prompt: string;
    starter_code: string;
    testcases: Testcase[];
    time_limit_ms?: number;
}

const props = defineProps<{
    blockId: number;
    content: CodeChallengeContent;
}>();

const { pyodideReady, pyodideLoading, pyodideError, loadPyodide, runCode } =
    usePyodide();

const userCode = ref(props.content.starter_code);
const isRunning = ref(false);
const runOutput = ref<RunResult | null>(null);
const testcaseResults = ref<
    Record<
        string,
        {
            passed: boolean;
            actual: string;
            expected: string;
            hidden: boolean;
        }
    >
>({});
const hasRunTests = ref(false);
const submitted = ref(false);

const allTestcasesPassed = computed(
    () =>
        hasRunTests.value &&
        Object.values(testcaseResults.value).every((r) => r.passed),
);

const passedCount = computed(
    () => Object.values(testcaseResults.value).filter((r) => r.passed).length,
);

const totalCount = computed(() => props.content.testcases.length);

async function runUserCode() {
    isRunning.value = true;
    runOutput.value = null;

    try {
        if (!pyodideReady.value) {
            await loadPyodide();
        }

        runOutput.value = await runCode(userCode.value);
    } finally {
        isRunning.value = false;
    }
}

async function runAllTestcases() {
    isRunning.value = true;
    testcaseResults.value = {};
    hasRunTests.value = false;

    try {
        if (!pyodideReady.value) {
            await loadPyodide();
        }

        for (const testcase of props.content.testcases) {
            const result = await runCode(userCode.value, testcase.input);
            const actual = result.stdout.trimEnd();
            const expected = testcase.expected_output.trimEnd();
            const passed = actual === expected && !result.error;

            testcaseResults.value[testcase.id] = {
                passed,
                actual,
                expected,
                hidden: testcase.hidden,
            };
        }

        hasRunTests.value = true;
    } finally {
        isRunning.value = false;
    }
}

function submitResult() {
    if (!hasRunTests.value) {
        return;
    }

    const isCorrect = allTestcasesPassed.value;
    const attemptData = Object.fromEntries(
        Object.entries(testcaseResults.value).map(([id, r]) => [
            id,
            { passed: r.passed },
        ]),
    );

    router.post(
        attemptRoutes.store.url(props.blockId),
        {
            answer: `${passedCount.value}/${totalCount.value}`,
            is_correct: isCorrect,
            attempt_data: attemptData,
            score: Math.round((passedCount.value / totalCount.value) * 100),
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
            },
        },
    );
}

function reset() {
    userCode.value = props.content.starter_code;
    runOutput.value = null;
    testcaseResults.value = {};
    hasRunTests.value = false;
    submitted.value = false;
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Tantangan Kode</h3>

            <p class="mt-2 text-sm text-gray-600">
                <Markdown :content="content.prompt" />
            </p>
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Kode Kamu</span>

                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="isRunning"
                        @click="runUserCode"
                    >
                        <Play v-if="!isRunning" class="h-3.5 w-3.5" />
                        <Loader2 v-else class="h-3.5 w-3.5 animate-spin" />
                        Run
                    </Button>

                    <Button
                        size="sm"
                        :disabled="isRunning"
                        @click="runAllTestcases"
                    >
                        <Loader2
                            v-if="isRunning"
                            class="h-3.5 w-3.5 animate-spin"
                        />
                        Run Testcases
                    </Button>
                </div>
            </div>

            <CodeEditor
                v-model="userCode"
                language="python"
                :readonly="submitted"
                placeholder="Tulis kode Python di sini..."
            />
        </div>

        <div
            v-if="pyodideError"
            class="rounded-lg bg-red-50 p-3 text-sm text-red-700"
        >
            Gagal memuat Pyodide: {{ pyodideError }}
        </div>

        <div
            v-if="pyodideLoading && !pyodideReady"
            class="rounded-lg bg-blue-50 p-3 text-sm text-blue-700"
        >
            Memuat Pyodide (~10MB)...
        </div>

        <div v-if="runOutput" class="overflow-hidden rounded-lg bg-gray-900">
            <div
                class="border-b border-gray-700 px-4 py-2 text-xs text-gray-400"
            >
                Output
            </div>

            <pre
                class="overflow-x-auto p-4 text-sm text-gray-100"
            ><code>{{ runOutput.stdout || '(kosong)' }}{{ runOutput.stderr }}{{ runOutput.error ? '\n--- Error ---\n' + runOutput.error : '' }}</code></pre>
        </div>

        <div v-if="hasRunTests">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">
                    Hasil Testcase ({{ passedCount }}/{{ totalCount }})
                </span>

                <div
                    v-if="allTestcasesPassed"
                    class="flex items-center gap-1 text-sm font-medium text-green-700"
                >
                    <CheckCircle2 class="h-4 w-4" />
                    Semua lulus
                </div>

                <div
                    v-else
                    class="flex items-center gap-1 text-sm font-medium text-red-700"
                >
                    <XCircle class="h-4 w-4" />
                    Ada testcase gagal
                </div>
            </div>

            <div class="space-y-2">
                <div
                    v-for="(testcase, index) in content.testcases"
                    :key="testcase.id"
                    class="rounded-lg border p-3"
                    :class="
                        testcaseResults[testcase.id]?.passed
                            ? 'border-green-200 bg-green-50'
                            : 'border-red-200 bg-red-50'
                    "
                >
                    <div class="flex items-center gap-2">
                        <CheckCircle2
                            v-if="testcaseResults[testcase.id]?.passed"
                            class="h-4 w-4 text-green-600"
                        />
                        <XCircle v-else class="h-4 w-4 text-red-600" />

                        <span class="text-sm font-medium text-gray-900">
                            Testcase {{ index + 1 }}
                            <span
                                v-if="testcase.hidden"
                                class="ml-1 text-xs text-gray-500"
                            >
                                (hidden)
                            </span>
                        </span>
                    </div>

                    <div v-if="!testcase.hidden" class="mt-2 space-y-1 text-xs">
                        <div>
                            <span class="font-medium text-gray-600"
                                >Input:</span
                            >
                            <pre
                                class="mt-1 rounded bg-white p-2 text-gray-800"
                            ><code>{{ testcase.input || '(kosong)' }}</code></pre>
                        </div>

                        <div>
                            <span class="font-medium text-gray-600"
                                >Expected:</span
                            >
                            <pre
                                class="mt-1 rounded bg-white p-2 text-gray-800"
                            ><code>{{ testcase.expected_output }}</code></pre>
                        </div>

                        <div>
                            <span class="font-medium text-gray-600"
                                >Output:</span
                            >
                            <pre
                                class="mt-1 rounded bg-white p-2 text-gray-800"
                            ><code>{{ testcaseResults[testcase.id]?.actual || '(kosong)' }}</code></pre>
                        </div>
                    </div>

                    <div v-else class="mt-2 text-xs text-gray-600">
                        {{
                            testcaseResults[testcase.id]?.passed
                                ? 'Output sesuai dengan yang diharapkan.'
                                : 'Output tidak sesuai dengan yang diharapkan.'
                        }}
                    </div>
                </div>
            </div>
        </div>

        <div v-if="hasRunTests && !submitted" class="flex gap-2">
            <Button :disabled="isRunning" @click="submitResult">
                Submit Hasil
            </Button>

            <Button variant="outline" @click="reset"> Reset </Button>
        </div>

        <div v-if="submitted" class="rounded-lg bg-green-50 p-4 text-green-700">
            <p class="font-medium">
                ✓ Tantangan selesai! Skor: {{ passedCount }}/{{ totalCount }}
                testcase lulus.
            </p>
        </div>
    </div>
</template>
