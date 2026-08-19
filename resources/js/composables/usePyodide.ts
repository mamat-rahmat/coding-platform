import { ref } from 'vue';

const PYODIDE_VERSION = '0.26.4';
const PYODIDE_CDN_URL = `https://cdn.jsdelivr.net/pyodide/v${PYODIDE_VERSION}/full/pyodide.js`;
const PYODIDE_BASE_URL = `https://cdn.jsdelivr.net/pyodide/v${PYODIDE_VERSION}/full/`;

type PyodideStdinHandler = () => string | null;
type PyodideStdoutHandler = (message: string) => void;

interface PyodideInstance {
    setStdin: (handlers: {
        stdin?: PyodideStdinHandler;
        isatty?: boolean;
    }) => void;
    setStdout: (handlers: {
        batched?: PyodideStdoutHandler;
        raw?: (charCode: number) => void;
    }) => void;
    setStderr: (handlers: {
        batched?: PyodideStdoutHandler;
        raw?: (charCode: number) => void;
    }) => void;
    runPythonAsync: (code: string) => Promise<unknown>;
    loadPackagesFromImports: (code: string) => Promise<void>;
    destroy: () => void;
}

declare global {
    interface Window {
        loadPyodide?: (config: {
            indexURL: string;
        }) => Promise<PyodideInstance>;
    }
}

export interface RunResult {
    stdout: string;
    stderr: string;
    error: string | null;
}

const pyodideReady = ref(false);
const pyodideLoading = ref(false);
const pyodideError = ref<string | null>(null);

let pyodide: PyodideInstance | null = null;
let loadPromise: Promise<void> | null = null;

function loadScript(src: string): Promise<void> {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) {
            resolve();

            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () =>
            reject(new Error(`Gagal memuat script Pyodide dari ${src}`));
        document.head.appendChild(script);
    });
}

export async function loadPyodide(): Promise<void> {
    if (pyodideReady.value && pyodide) {
        return;
    }

    if (loadPromise) {
        return loadPromise;
    }

    pyodideLoading.value = true;
    pyodideError.value = null;

    loadPromise = (async () => {
        try {
            await loadScript(PYODIDE_CDN_URL);

            if (!window.loadPyodide) {
                throw new Error(
                    'loadPyodide tidak tersedia setelah script dimuat.',
                );
            }

            pyodide = await window.loadPyodide({ indexURL: PYODIDE_BASE_URL });
            pyodideReady.value = true;
        } catch (err) {
            const message = err instanceof Error ? err.message : String(err);
            pyodideError.value = message;
            pyodideReady.value = false;
            pyodide = null;
            loadPromise = null;

            throw err;
        } finally {
            pyodideLoading.value = false;
        }
    })();

    return loadPromise;
}

export async function runCode(
    code: string,
    stdin?: string,
): Promise<RunResult> {
    if (!pyodide) {
        await loadPyodide();
    }

    if (!pyodide) {
        return {
            stdout: '',
            stderr: '',
            error: 'Pyodide belum siap.',
        };
    }

    let stdoutBuffer = '';
    let stderrBuffer = '';

    pyodide.setStdout({
        batched: (message: string) => {
            stdoutBuffer += message;
        },
    });

    pyodide.setStderr({
        batched: (message: string) => {
            stderrBuffer += message;
        },
    });

    if (stdin !== undefined && stdin.length > 0) {
        const lines = stdin.split('\n');
        let lineIndex = 0;
        pyodide.setStdin({
            stdin: () => {
                if (lineIndex < lines.length) {
                    return lines[lineIndex++];
                }

                return null;
            },
            isatty: false,
        });
    } else {
        pyodide.setStdin({
            stdin: () => null,
            isatty: false,
        });
    }

    try {
        await pyodide.loadPackagesFromImports(code);
        await pyodide.runPythonAsync(code);

        return {
            stdout: stdoutBuffer,
            stderr: stderrBuffer,
            error: null,
        };
    } catch (err) {
        const message = err instanceof Error ? err.message : String(err);

        return {
            stdout: stdoutBuffer,
            stderr: stderrBuffer,
            error: message,
        };
    }
}

export function usePyodide() {
    return {
        pyodideReady,
        pyodideLoading,
        pyodideError,
        loadPyodide,
        runCode,
    };
}
