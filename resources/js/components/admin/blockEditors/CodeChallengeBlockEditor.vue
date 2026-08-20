<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import CodeEditor from '@/components/CodeEditor.vue';
import Markdown from '@/components/Markdown.vue';
import { Button } from '@/components/ui/button';

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

const model = defineModel<CodeChallengeContent>({ required: true });

if (typeof model.value.prompt !== 'string') {
    model.value.prompt = '';
}

if (typeof model.value.starter_code !== 'string') {
    model.value.starter_code = '';
}

if (!Array.isArray(model.value.testcases)) {
    model.value.testcases = [];
}

function addTestcase() {
    const nextId = `tc${model.value.testcases.length + 1}`;
    model.value.testcases.push({
        id: nextId,
        input: '',
        expected_output: '',
        hidden: false,
    });
}

function removeTestcase(index: number) {
    model.value.testcases.splice(index, 1);
}
</script>

<template>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Prompt Soal (Markdown)
                </label>
                <textarea
                    v-model="model.prompt"
                    class="h-48 w-full rounded-md border border-gray-300 p-2 font-mono text-sm focus:border-gray-900 focus:outline-none"
                    rows="4"
                />
            </div>

            <div class="flex flex-col">
                <span class="mb-1 block text-sm font-medium text-gray-700">
                    Preview
                </span>
                <div
                    class="h-48 flex-1 overflow-y-auto rounded-md border border-dashed border-gray-300 bg-gray-50 p-3"
                >
                    <p
                        v-if="(model.prompt || '').trim() === ''"
                        class="text-sm text-gray-400"
                    >
                        Preview akan tampil di sini.
                    </p>
                    <Markdown v-else :content="model.prompt" />
                </div>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Starter Code
            </label>
            <CodeEditor v-model="model.starter_code" language="python" />
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">
                    Testcases
                </label>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addTestcase"
                >
                    <Plus class="h-3.5 w-3.5" />
                    Tambah Testcase
                </Button>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(tc, index) in model.testcases"
                    :key="index"
                    class="rounded-lg border p-3"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <span class="font-mono text-xs">
                            {{ tc.id }}
                        </span>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1 text-xs">
                                <input
                                    v-model="tc.hidden"
                                    type="checkbox"
                                    class="h-3.5 w-3.5"
                                />
                                Hidden
                            </label>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon-sm"
                                @click="removeTestcase(index)"
                            >
                                <Trash2 class="h-4 w-4 text-red-600" />
                            </Button>
                        </div>
                    </div>

                    <div class="grid gap-2 md:grid-cols-2">
                        <div>
                            <label class="text-xs text-gray-600">Input</label>
                            <textarea
                                v-model="tc.input"
                                class="w-full rounded-md border border-gray-300 p-2 font-mono text-xs focus:border-gray-900 focus:outline-none"
                                rows="3"
                            />
                        </div>

                        <div>
                            <label class="text-xs text-gray-600">
                                Expected Output
                            </label>
                            <textarea
                                v-model="tc.expected_output"
                                class="w-full rounded-md border border-gray-300 p-2 font-mono text-xs focus:border-gray-900 focus:outline-none"
                                rows="3"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Time Limit (ms, opsional)
            </label>
            <input
                v-model.number="model.time_limit_ms"
                type="number"
                min="0"
                class="w-32 rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
            />
        </div>
    </div>
</template>
