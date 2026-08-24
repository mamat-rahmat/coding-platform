<script setup lang="ts">
import { Loader2, Play, Terminal } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import CodeEditor from '@/components/CodeEditor.vue';
import Markdown from '@/components/Markdown.vue';
import { Button } from '@/components/ui/button';
import { usePyodideTerminal } from '@/composables/usePyodideTerminal';
import attemptRoutes from '@/routes/lesson-blocks/attempts';

interface CodeExampleContent {
    language: string;
    code: string;
    markdown?: string;
}

const props = defineProps<{
    blockId: number;
    content: CodeExampleContent;
}>();

const emit = defineEmits<{
    run: [];
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

const terminalContainer = ref<HTMLElement | null>(null);

onMounted(() => {
    if (terminalContainer.value) {
        init(terminalContainer.value);
    }
});

onUnmounted(() => {
    dispose();
});

async function runExample() {
    if (!pyodideReady.value || isRunning.value) {
        return;
    }

    clear();
    write('\x1b[33m--- Running ---\x1b[0m\r\n');
    await runCode(props.content.code);

    fetch(attemptRoutes.store.url(props.blockId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': decodeURIComponent(
                document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
            ),
        },
        body: JSON.stringify({ answer: '' }),
    });

    emit('run');
}
</script>

<template>
    <div class="overflow-hidden rounded-lg">
        <Markdown
            v-if="content.markdown"
            :content="content.markdown"
            class="mb-3"
        />

        <div class="overflow-hidden rounded-lg">
            <div
                class="flex items-center justify-between border-b border-gray-700 bg-gray-900 px-4 py-2"
            >
                <span class="text-xs text-gray-400">{{
                    content.language
                }}</span>

                <div class="flex items-center gap-2">
                    <span class="text-xs tracking-wide text-gray-400 uppercase">
                        Contoh
                    </span>

                    <Button
                        variant="outline"
                        size="sm"
                        class="border-gray-600 bg-gray-800 text-gray-200 hover:bg-gray-700 hover:text-white"
                        :disabled="!pyodideReady || isRunning"
                        @click="runExample"
                    >
                        <Play v-if="!isRunning" class="h-3.5 w-3.5" />
                        <Loader2 v-else class="h-3.5 w-3.5 animate-spin" />
                        Run
                    </Button>
                </div>
            </div>

            <CodeEditor
                :model-value="content.code"
                :language="content.language"
                readonly
            />

            <div
                v-if="pyodideLoading && !pyodideReady"
                class="border-t border-gray-700 bg-gray-900 px-4 py-2 text-xs text-blue-300"
            >
                Memuat Pyodide di Web Worker (~10MB)...
            </div>

            <div
                v-if="pyodideError"
                class="border-t border-gray-700 bg-gray-900 px-4 py-2 text-xs text-red-400"
            >
                Error: {{ pyodideError }}
            </div>

            <div class="border-t border-gray-700 bg-gray-900 px-4 py-2">
                <div
                    v-if="pyodideReady"
                    class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-400"
                >
                    <Terminal class="h-3.5 w-3.5" />
                    Output

                    <span
                        v-if="isInteractive"
                        class="rounded-full bg-green-900/40 px-2 py-0.5 text-green-400"
                    >
                        Interaktif
                    </span>
                    <span
                        v-else
                        class="rounded-full bg-yellow-900/40 px-2 py-0.5 text-yellow-400"
                    >
                        Non-interaktif
                    </span>
                </div>

                <div
                    ref="terminalContainer"
                    class="h-48 overflow-hidden rounded border border-gray-800 bg-black p-2"
                />
            </div>
        </div>
    </div>
</template>
