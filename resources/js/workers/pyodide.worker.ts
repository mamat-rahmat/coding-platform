/// <reference lib="webworker" />

const PYODIDE_VERSION = '0.26.4';
const PYODIDE_BASE_URL = `https://cdn.jsdelivr.net/pyodide/v${PYODIDE_VERSION}/full/`;

let pyodide: any = null;
let sab: SharedArrayBuffer | null = null;
let sabView: Int32Array | null = null;
const STDIN_BUFFER_SIZE = 1024;

async function loadPyodideInstance(): Promise<void> {
    if (pyodide) {
return;
}

    const mod = await import(
        /* @vite-ignore */ `${PYODIDE_BASE_URL}pyodide.mjs`
    );
    pyodide = await mod.loadPyodide({
        indexURL: PYODIDE_BASE_URL,
    });
}

function setupStdinWithSAB(buffer: SharedArrayBuffer): void {
    sab = buffer;
    sabView = new Int32Array(buffer);

    pyodide.setStdin({
        stdin: () => {
            if (!sabView) {
return null;
}

            Atomics.store(sabView, 0, 0);

            self.postMessage({ type: 'stdin_request' });

            Atomics.wait(sabView, 0, 0);

            const length = Atomics.load(sabView, 1);

            if (length < 0) {
return null;
}

            const bytes = new Uint8Array(sab!, 8, length);
            const line = new TextDecoder().decode(bytes);

            return line;
        },
        isatty: true,
    });
}

function setupStdout(): void {
    pyodide.setStdout({
        raw: (charCode: number) => {
            self.postMessage({
                type: 'stdout',
                data: String.fromCharCode(charCode),
            });
        },
    });

    pyodide.setStderr({
        raw: (charCode: number) => {
            self.postMessage({
                type: 'stderr',
                data: String.fromCharCode(charCode),
            });
        },
    });
}

async function runCode(code: string): Promise<void> {
    try {
        await pyodide.loadPackagesFromImports(code);
        await pyodide.runPythonAsync(code);
        self.postMessage({ type: 'done' });
    } catch (err) {
        const message = err instanceof Error ? err.message : String(err);
        self.postMessage({ type: 'error', data: message });
    }
}

async function runTestcase(
    code: string,
    input: string,
    expectedOutput: string,
    testcaseId: string,
): Promise<void> {
    const lines = input.split('\n');
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

    let output = '';

    pyodide.setStdout({
        raw: (charCode: number) => {
            output += String.fromCharCode(charCode);
        },
    });

    pyodide.setStderr({
        raw: () => {},
    });

    let error: string | null = null;

    try {
        await pyodide.loadPackagesFromImports(code);
        await pyodide.runPythonAsync(code);
    } catch (err) {
        error = err instanceof Error ? err.message : String(err);
    }

    const passed = !error && output.trimEnd() === expectedOutput.trimEnd();

    self.postMessage({
        type: 'testcase_result',
        testcaseId,
        passed,
        actual: output.trimEnd(),
        expected: expectedOutput.trimEnd(),
        error,
    });
}

self.onmessage = async (e: MessageEvent) => {
    const { type, data, buffer, code, input, expectedOutput, testcaseId } =
        e.data;

    switch (type) {
        case 'init_sab':
            await loadPyodideInstance();
            setupStdinWithSAB(buffer);
            setupStdout();
            self.postMessage({ type: 'ready' });
            break;

        case 'run':
            await runCode(data);
            break;

        case 'run_testcase':
            await runTestcase(code, input, expectedOutput, testcaseId);
            break;

        case 'stdin_response':
            if (!sabView) {
break;
}

            {
                const encoded = new TextEncoder().encode(data);
                const length = Math.min(encoded.length, STDIN_BUFFER_SIZE);
                const bytes = new Uint8Array(sab!, 8, length);
                bytes.set(encoded.subarray(0, length));

                Atomics.store(sabView, 1, length);
                Atomics.store(sabView, 0, 1);
                Atomics.notify(sabView, 0);
            }
            break;

        case 'stdin_eof':
            if (!sabView) {
break;
}

            Atomics.store(sabView, 1, -1);
            Atomics.store(sabView, 0, 1);
            Atomics.notify(sabView, 0);
            break;
    }
};
