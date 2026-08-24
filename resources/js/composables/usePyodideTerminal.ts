import { FitAddon } from '@xterm/addon-fit';
import { WebLinksAddon } from '@xterm/addon-web-links';
import type { Terminal as TerminalType } from '@xterm/xterm';
import { ref, onUnmounted } from 'vue';
import '@xterm/xterm/css/xterm.css';

const STDIN_BUFFER_SIZE = 1024;
const SAB_TOTAL_SIZE = 8 + STDIN_BUFFER_SIZE;

const PYODIDE_VERSION = '0.26.4';
const PYODIDE_BASE_URL = `https://cdn.jsdelivr.net/pyodide/v${PYODIDE_VERSION}/full/`;

const WORKER_CODE = `
const PYODIDE_BASE_URL = "${PYODIDE_BASE_URL}";
let pyodide = null;
let sab = null;
let sabView = null;
const STDIN_BUFFER_SIZE = ${STDIN_BUFFER_SIZE};

async function loadPyodideInstance() {
    if (pyodide) return;
    importScripts(PYODIDE_BASE_URL + "pyodide.js");
    pyodide = await globalThis.loadPyodide({ indexURL: PYODIDE_BASE_URL });
}

function setupStdinWithSAB(buffer) {
    sab = buffer;
    sabView = new Int32Array(buffer);
    pyodide.setStdin({
        stdin: () => {
            if (!sabView) return null;
            Atomics.store(sabView, 0, 0);
            self.postMessage({ type: "stdin_request" });
            Atomics.wait(sabView, 0, 0);
            const length = Atomics.load(sabView, 1);
            if (length < 0) return null;
            const bytes = new Uint8Array(sab, 8, length);
            const copy = new Uint8Array(length);
            copy.set(bytes);
            return new TextDecoder().decode(copy);
        },
        isatty: true,
    });
}

function setupStdout() {
    pyodide.setStdout({
        raw: (charCode) => self.postMessage({ type: "stdout", data: String.fromCharCode(charCode) }),
    });
    pyodide.setStderr({
        raw: (charCode) => self.postMessage({ type: "stderr", data: String.fromCharCode(charCode) }),
    });
}

async function runCode(code) {
    setupStdout();
    if (sab) setupStdinWithSAB(sab);
    try {
        await pyodide.loadPackagesFromImports(code);
        await pyodide.runPythonAsync(code);
        self.postMessage({ type: "done" });
    } catch (err) {
        self.postMessage({ type: "error", data: err instanceof Error ? err.message : String(err) });
    }
}

async function runTestcase(code, input, expectedOutput, testcaseId) {
    const lines = (input ?? "").split("\\n");
    let lineIndex = 0;
    pyodide.setStdin({
        stdin: () => lineIndex < lines.length ? lines[lineIndex++] : null,
        isatty: false,
    });
    let output = "";
    pyodide.setStdout({ raw: (c) => { output += String.fromCharCode(c); } });
    pyodide.setStderr({ raw: () => {} });
    let error = null;
    try {
        await pyodide.loadPackagesFromImports(code);
        await pyodide.runPythonAsync(code);
    } catch (err) {
        error = err instanceof Error ? err.message : String(err);
    }
    const passed = !error && output.trimEnd() === (expectedOutput ?? "").trimEnd();
    self.postMessage({ type: "testcase_result", testcaseId, passed, actual: output.trimEnd(), expected: (expectedOutput ?? "").trimEnd(), error });
}

self.onmessage = async (e) => {
    const { type, data, buffer, code, input, expectedOutput, testcaseId } = e.data;
    switch (type) {
        case "init_sab":
            await loadPyodideInstance();
            setupStdinWithSAB(buffer);
            setupStdout();
            self.postMessage({ type: "ready" });
            break;
        case "init_no_sab":
            await loadPyodideInstance();
            setupStdout();
            self.postMessage({ type: "ready" });
            break;
        case "run":
            await runCode(data);
            break;
        case "run_testcase":
            await runTestcase(code, input, expectedOutput, testcaseId);
            break;
        case "stdin_response":
            if (!sabView) break;
            {
                const encoded = new TextEncoder().encode(data);
                const length = Math.min(encoded.length, STDIN_BUFFER_SIZE);
                const bytes = new Uint8Array(sab, 8, length);
                bytes.set(encoded.subarray(0, length));
                Atomics.store(sabView, 1, length);
                Atomics.store(sabView, 0, 1);
                Atomics.notify(sabView, 0);
            }
            break;
        case "stdin_eof":
            if (!sabView) break;
            Atomics.store(sabView, 1, -1);
            Atomics.store(sabView, 0, 1);
            Atomics.notify(sabView, 0);
            break;
    }
};
`;

function createWorker(): Worker {
    const blob = new Blob([WORKER_CODE], { type: 'application/javascript' });
    const url = URL.createObjectURL(blob);

    return new Worker(url);
}

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
    stop: () => void;
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

    let term: TerminalType | null = null;
    let fitAddon: FitAddon | null = null;
    let worker: Worker | null = null;
    let sab: SharedArrayBuffer | null = null;
    let sabView: Int32Array | null = null;
    let resizeObserver: ResizeObserver | null = null;

    let stdinBuffer = '';
    let stdinResolve: ((value: string) => void) | null = null;
    let testcaseResolve: ((result: TestcaseResult) => void) | null = null;
    let pendingStdin: string | null = null;
    let messageEpoch = 0;

    async function init(container: HTMLElement): Promise<void> {
        const { Terminal } = await import('@xterm/xterm');
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
        messageEpoch++;
        pyodideLoading.value = true;
        pyodideError.value = null;

        const sabAvailable = isSABAvailable();
        isInteractive.value = sabAvailable;

        try {
            worker = createWorker();
        } catch (err) {
            const msg = err instanceof Error ? err.message : String(err);
            pyodideError.value = `Gagal membuat Worker: ${msg}`;
            pyodideLoading.value = false;
            term?.write(`\r\n\x1b[31mError: ${msg}\x1b[0m\r\n`);

            return;
        }

        worker.onmessage = createMessageHandler(messageEpoch);

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

    function createMessageHandler(epoch: number) {
        return function handleWorkerMessage(e: MessageEvent): void {
            if (epoch !== messageEpoch) {
                return;
            }

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
                    term?.write('\r\n\x1b[33m--- Stopped ---\x1b[0m\r\n');
                    isRunning.value = false;
                    break;

                case 'error':
                    term?.write(`\r\n\x1b[31m${msg.data}\x1b[0m\r\n`);
                    term?.write('\r\n\x1b[33m--- Stopped ---\x1b[0m\r\n');
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
        };
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

    function stop(): void {
        if (worker) {
            worker.terminate();
            worker = null;
        }

        term?.clear();
        isRunning.value = false;
        pyodideReady.value = false;
        pyodideLoading.value = false;
        initWorker();
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
        stop,
        clear,
        write,
        dispose,
    };
}
