<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Play, Loader2, RotateCcw } from '@lucide/vue';
import { ref } from 'vue';
import CodeEditor from '@/components/CodeEditor.vue';
import { Button } from '@/components/ui/button';
import { usePyodide  } from '@/composables/usePyodide';
import type {RunResult} from '@/composables/usePyodide';
import playgroundRoutes from '@/routes/playground';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Playground',
                href: playgroundRoutes.index(),
            },
        ],
    },
});

const props = defineProps<{
    starterCode: string;
}>();

const { pyodideReady, pyodideLoading, pyodideError, loadPyodide, runCode } =
    usePyodide();

const code = ref<string>(props.starterCode);
const stdin = ref('');
const result = ref<RunResult | null>(null);
const isRunning = ref(false);

async function run() {
    isRunning.value = true;
    result.value = null;

    try {
        if (!pyodideReady.value) {
            await loadPyodide();
        }

        result.value = await runCode(code.value, stdin.value);
    } finally {
        isRunning.value = false;
    }
}

function reset() {
    code.value = '';
    stdin.value = '';
    result.value = null;
}
</script>

<template>
    <Head title="Playground" />

    <div class="flex h-full flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Playground</h1>

                <p class="text-sm text-gray-500">
                    Eksekusi kode Python langsung di browser via Pyodide.
                </p>
            </div>

            <div class="flex gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="isRunning"
                    @click="reset"
                >
                    <RotateCcw class="h-3.5 w-3.5" />
                    Reset
                </Button>

                <Button size="sm" :disabled="isRunning" @click="run">
                    <Play v-if="!isRunning" class="h-3.5 w-3.5" />
                    <Loader2 v-else class="h-3.5 w-3.5 animate-spin" />
                    Run
                </Button>
            </div>
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

        <div class="grid flex-1 gap-4 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700">Kode</label>

                <div class="flex-1 overflow-hidden rounded-lg border">
                    <CodeEditor
                        v-model="code"
                        language="python"
                        autofocus
                        placeholder="Tulis kode Python di sini..."
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Stdin (input)
                    </label>

                    <textarea
                        v-model="stdin"
                        class="w-full rounded-lg border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
                        rows="3"
                        placeholder="Pisah baris dengan newline (Enter)..."
                    />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-gray-700">Output</label>

                <div class="flex-1 overflow-auto rounded-lg bg-gray-900 p-4">
                    <pre
                        v-if="result"
                        class="font-mono text-sm break-words whitespace-pre-wrap text-gray-100"
                    ><code>{{ result.stdout || '(kosong)' }}{{ result.stderr ? '\n--- stderr ---\n' + result.stderr : '' }}{{ result.error ? '\n--- error ---\n' + result.error : '' }}</code></pre>

                    <div
                        v-else
                        class="flex h-full items-center justify-center text-sm text-gray-500"
                    >
                        Tekan Run untuk menjalankan kode.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
