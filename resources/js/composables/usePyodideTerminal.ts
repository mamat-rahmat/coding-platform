import { FitAddon } from '@xterm/addon-fit';
import { WebLinksAddon } from '@xterm/addon-web-links';
import { Terminal } from '@xterm/xterm';
import { ref, onUnmounted } from 'vue';
import '@xterm/xterm/css/xterm.css';

const STDIN_BUFFER_SIZE = 1024;
const SAB_TOTAL_SIZE = 8 + STDIN_BUFFER_SIZE;

export interface TestcaseResult {
    testcaseId: string;
    passed: boolean;
    actual: string;
    expected: string;
    error: string | null;
}

export interface UsePyodideTerminalReturn {
    pyodideReady: ReturnType<typeof ref<boolean>>;
    pyodideLoading: ReturnType<typeof ref<boolean>>;
    pyodideError: ReturnType<typeof ref<string | null>>;
    isInteractive: ReturnType<typeof ref<boolean>>;
    isRunning: ReturnType<typeof ref<boolean>>;
    init: (container: HTMLElement) => void;
    runCode: (code: string, stdin?: string) => Promise<void>;
    runTestcase: (
        code: string,
        input: string,
        expectedOutput: string,
        testcaseId: string,
    ) => Promise<TestcaseResult>;
    clear: () => void;
    write: (text: string) => void;
    dispose: () => void;
}

function isSABAvailable(): boolean {
    return (
        typeof SharedArrayBuffer !== 'undefined' &&
        typeof self !== 'undefined' &&
        self.crossOriginIsolated === true
    );
}

export function usePyodideTerminal(): UsePyodideTerminalReturn {
    const pyodideReady = ref(false);
    const pyodideLoading = ref(false);
    const pyodideError = ref<string | null>(null);
    const isInteractive = ref(false);
    const isRunning = ref(false);

    let term: Terminal | null = null;
    let fitAddon: FitAddon | null = null;
    let worker: Worker | null = null;
    let sab: SharedArrayBuffer | null = null;
    let sabView: Int32Array | null = null;
    let resizeObserver: ResizeObserver | null = null;

    let stdinBuffer = '';
    let stdinResolve: ((value: string) => void) | null = null;
    let testcaseResolve: ((result: TestcaseResult) => void) | null = null;
    let pendingStdin: string | null = null;

    function init(container: HTMLElement): void {
        term = new Terminal({
            fontSize: 13,
            fontFamily:
                'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
            theme: {
                background: '#1f2937',
                foreground: '#f9fafb',
                cursor: '#f9fafb',
                selectionBackground: '#374151',
            },
            convertEol: true,
        });

        fitAddon = new FitAddon();
        term.loadAddon(fitAddon);
        term.loadAddon(new WebLinksAddon());

        term.open(container);
        fitAddon.fit();

        resizeObserver = new ResizeObserver(() => {
            fitAddon?.fit();
        });
        resizeObserver.observe(container);

        term.onData(handleTerminalInput);

        initWorker();
    }

    function initWorker(): void {
        pyodideLoading.value = true;
        pyodideError.value = null;

        const sabAvailable = isSABAvailable();
        isInteractive.value = sabAvailable;

        try {
            worker = new Worker(
                new URL('../workers/pyodide.worker.ts', import.meta.url),
                { type: 'module' },
            );
        } catch (err) {
            const msg = err instanceof Error ? err.message : String(err);
            pyodideError.value = `Gagal membuat Worker: ${msg}`;
            pyodideLoading.value = false;
            term?.write(`\r\n\x1b[31mError: ${msg}\x1b[0m\r\n`);

            return;
        }

        worker.onmessage = handleWorkerMessage;

        worker.onerror = (err: ErrorEvent) => {
            const msg = err.message || 'Unknown worker error';
            pyodideError.value = msg;
            pyodideLoading.value = false;
            term?.write(`\r\n\x1b[31mWorker Error: ${msg}\x1b[0m\r\n`);
        };

        if (sabAvailable) {
            try {
                sab = new SharedArrayBuffer(SAB_TOTAL_SIZE);
                sabView = new Int32Array(sab);

                worker.postMessage({
                    type: 'init_sab',
                    buffer: sab,
                });
            } catch (err) {
                const msg = err instanceof Error ? err.message : String(err);
                pyodideError.value = `SharedArrayBuffer gagal: ${msg}`;
                isInteractive.value = false;

                worker.postMessage({ type: 'init_no_sab' });
            }
        } else {
            worker.postMessage({ type: 'init_no_sab' });
        }
    }

    function handleWorkerMessage(e: MessageEvent): void {
        const msg = e.data;

        switch (msg.type) {
            case 'ready':
                pyodideReady.value = true;
                pyodideLoading.value = false;
                break;

            case 'stdout':
            case 'stderr':
                term?.write(msg.data);
                break;

            case 'stdin_request':
                promptStdin();
                break;

            case 'done':
                isRunning.value = false;
                break;

            case 'error':
                term?.write(`\r\n\x1b[31m${msg.data}\x1b[0m\r\n`);
                isRunning.value = false;
                break;

            case 'testcase_result':
                isRunning.value = false;

                if (testcaseResolve) {
                    testcaseResolve({
                        testcaseId: msg.testcaseId,
                        passed: msg.passed,
                        actual: msg.actual,
                        expected: msg.expected,
                        error: msg.error,
                    });
                    testcaseResolve = null;
                }

                break;
        }
    }

    function handleTerminalInput(data: string): void {
        if (!stdinResolve) {
return;
}

        for (const ch of data) {
            if (ch === '\r') {
                term?.write('\r\n');
                stdinBuffer += '\n';
                const line = stdinBuffer;
                stdinBuffer = '';
                const resolve = stdinResolve;
                stdinResolve = null;
                resolve(line);

                return;
            }

            if (ch === '\x7f') {
                if (stdinBuffer.length > 0) {
                    stdinBuffer = stdinBuffer.slice(0, -1);
                    term?.write('\b \b');
                }

                return;
            }

            if (ch === '\x03') {
                stdinBuffer = '';
                term?.write('^C\r\n');
                const resolve = stdinResolve;
                stdinResolve = null;
                resolve('');

                return;
            }

            term?.write(ch);
            stdinBuffer += ch;
        }
    }

    function promptStdin(): void {
        if (!worker || !sabView) {
            if (pendingStdin !== null) {
                const line = pendingStdin;
                pendingStdin = null;

                if (stdinResolve) {
                    const resolve = stdinResolve;
                    stdinResolve = null;
                    resolve(line);
                }
            }

            return;
        }

        new Promise<string>((resolve) => {
            stdinResolve = resolve;
        }).then((line) => {
            if (sabView && worker) {
                const encoded = new TextEncoder().encode(line);
                const length = Math.min(encoded.length, STDIN_BUFFER_SIZE);
                const bytes = new Uint8Array(sab!, 8, length);
                bytes.set(encoded.subarray(0, length));

                Atomics.store(sabView, 1, length);
                Atomics.store(sabView, 0, 1);
                Atomics.notify(sabView, 0);
            }
        });
    }

    async function runCode(code: string, stdin?: string): Promise<void> {
        if (!worker || !pyodideReady.value) {
            return;
        }

        isRunning.value = true;

        if (!isInteractive.value && stdin) {
            pendingStdin = stdin;
        }

        worker.postMessage({ type: 'run', data: code });
    }

    async function runTestcase(
        code: string,
        input: string,
        expectedOutput: string,
        testcaseId: string,
    ): Promise<TestcaseResult> {
        if (!worker || !pyodideReady.value) {
            return {
                testcaseId,
                passed: false,
                actual: '',
                expected: expectedOutput,
                error: 'Pyodide belum siap',
            };
        }

        isRunning.value = true;

        return new Promise<TestcaseResult>((resolve) => {
            testcaseResolve = resolve;
            worker!.postMessage({
                type: 'run_testcase',
                code,
                input,
                expectedOutput,
                testcaseId,
            });
        });
    }

    function clear(): void {
        term?.clear();
    }

    function write(text: string): void {
        term?.write(text);
    }

    function dispose(): void {
        worker?.terminate();
        worker = null;
        resizeObserver?.disconnect();
        resizeObserver = null;
        term?.dispose();
        term = null;
        pyodideReady.value = false;
        pyodideLoading.value = false;
    }

    onUnmounted(() => {
        dispose();
    });

    return {
        pyodideReady,
        pyodideLoading,
        pyodideError,
        isInteractive,
        isRunning,
        init,
        runCode,
        runTestcase,
        clear,
        write,
        dispose,
    };
}
