<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Play, Loader2, RotateCcw, Terminal } from '@lucide/vue';
import { ref, onMounted, onUnmounted } from 'vue';
import CodeEditor from '@/components/CodeEditor.vue';
import { Button } from '@/components/ui/button';
import { usePyodideTerminal } from '@/composables/usePyodideTerminal';
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

const {
    pyodideReady,
    pyodideLoading,
    pyodideError,
    isInteractive,
    isRunning,
    init,
    runCode,
    clear,
    write,
    dispose,
} = usePyodideTerminal();

const code = ref<string>(props.starterCode);
const terminalContainer = ref<HTMLElement | null>(null);

onMounted(() => {
    if (terminalContainer.value) {
        init(terminalContainer.value);
        write(
            '\r\nPython Playground — ketik kode di editor lalu tekan Run.\r\n\r\n',
        );
    }
});

onUnmounted(() => {
    dispose();
});

async function run() {
    if (!pyodideReady.value || isRunning.value) {
        return;
    }

    clear();
    write('\x1b[33m--- Running ---\x1b[0m\r\n');
    await runCode(code.value);
}

function reset() {
    code.value = props.starterCode;
    clear();
    write('\r\nTerminal cleared.\r\n');
}
</script>

<template>
    <Head title="Playground" />

    <div class="flex h-full flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Playground</h1>
                <p class="text-sm text-gray-500">
                    Eksekusi kode Python secara interaktif via terminal.
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

                <Button
                    size="sm"
                    :disabled="!pyodideReady || isRunning"
                    @click="run"
                >
                    <Play v-if="!isRunning" class="h-3.5 w-3.5" />
                    <Loader2 v-else class="h-3.5 w-3.5 animate-spin" />
                    Run
                </Button>
            </div>
        </div>

        <div
            v-if="pyodideLoading && !pyodideReady"
            class="rounded-lg bg-blue-50 p-3 text-sm text-blue-700"
        >
            Memuat Pyodide di Web Worker (~10MB)...
        </div>

        <div
            v-if="pyodideError"
            class="rounded-lg bg-red-50 p-3 text-sm text-red-700"
        >
            Error: {{ pyodideError }}
        </div>

        <div class="grid flex-1 gap-4 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <div
                    class="flex items-center gap-2 text-sm font-medium text-gray-700"
                >
                    <span>Kode</span>
                    <span
                        v-if="pyodideReady"
                        class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700"
                    >
                        {{ isInteractive ? 'Interaktif' : 'Siap' }}
                    </span>
                </div>

                <div class="flex-1 overflow-hidden rounded-lg border">
                    <CodeEditor
                        v-model="code"
                        language="python"
                        autofocus
                        placeholder="Tulis kode Python di sini..."
                    />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div
                    class="flex items-center gap-2 text-sm font-medium text-gray-700"
                >
                    <Terminal class="h-4 w-4" />
                    Terminal
                </div>

                <div
                    ref="terminalContainer"
                    class="flex-1 overflow-hidden rounded-lg border border-gray-700 bg-gray-900 p-2"
                />
            </div>
        </div>
    </div>
</template>
