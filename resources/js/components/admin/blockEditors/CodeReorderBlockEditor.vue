<script setup lang="ts">
import { Plus, Trash2, ArrowUp, ArrowDown } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';

interface CodeReorderContent {
    lines: string[];
    correct_order: number[];
}

const model = defineModel<CodeReorderContent>({ required: true });

if (!Array.isArray(model.value.lines)) {
    model.value.lines = [''];
}

if (!Array.isArray(model.value.correct_order)) {
    model.value.correct_order = [0];
}

const correctOrderInput = ref(model.value.correct_order.join(', '));

const syncCorrectOrder = () => {
    model.value.correct_order = correctOrderInput.value
        .split(',')
        .map((s) => parseInt(s.trim(), 10))
        .filter((n) => !Number.isNaN(n));
};

function addLine() {
    model.value.lines.push('');
}

function removeLine(index: number) {
    model.value.lines.splice(index, 1);
}

function moveUp(index: number) {
    if (index === 0) {
        return;
    }

    [model.value.lines[index - 1], model.value.lines[index]] = [
        model.value.lines[index],
        model.value.lines[index - 1],
    ];
}

function moveDown(index: number) {
    if (index === model.value.lines.length - 1) {
        return;
    }

    [model.value.lines[index + 1], model.value.lines[index]] = [
        model.value.lines[index],
        model.value.lines[index + 1],
    ];
}

const autoFillCorrect = () => {
    correctOrderInput.value = model.value.lines.map((_, i) => i).join(', ');
    syncCorrectOrder();
};
</script>

<template>
    <div class="space-y-4">
        <div>
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">
                    Baris Kode (urutan ini = urutan benar)
                </label>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addLine"
                >
                    <Plus class="h-3.5 w-3.5" />
                    Tambah Baris
                </Button>
            </div>

            <div class="space-y-2">
                <div
                    v-for="(line, index) in model.lines"
                    :key="index"
                    class="flex items-center gap-2"
                >
                    <span class="w-6 font-mono text-xs text-gray-500">
                        {{ index }}
                    </span>
                    <input
                        v-model="model.lines[index]"
                        class="flex-1 rounded-md border border-gray-300 p-2 font-mono text-sm focus:border-gray-900 focus:outline-none"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        :disabled="index === 0"
                        @click="moveUp(index)"
                    >
                        <ArrowUp class="h-4 w-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        :disabled="index === model.lines.length - 1"
                        @click="moveDown(index)"
                    >
                        <ArrowDown class="h-4 w-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        @click="removeLine(index)"
                    >
                        <Trash2 class="h-4 w-4 text-red-600" />
                    </Button>
                </div>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Correct Order (indeks baris, dipisah koma)
            </label>
            <div class="flex gap-2">
                <input
                    v-model="correctOrderInput"
                    class="flex-1 rounded-md border border-gray-300 p-2 font-mono text-sm focus:border-gray-900 focus:outline-none"
                    placeholder="0,1,2"
                    @blur="syncCorrectOrder"
                />
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="autoFillCorrect"
                >
                    Auto (sequential)
                </Button>
            </div>
            <p class="mt-1 text-xs text-gray-500">
                Indeks merefer ke posisi di list atas (0-based). Contoh: "1,0,2"
                = baris index 1 → posisi 1, baris 0 → posisi 2, dst.
            </p>
        </div>
    </div>
</template>
