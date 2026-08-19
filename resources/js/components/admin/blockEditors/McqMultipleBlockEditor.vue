<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';

interface Option {
    id: string;
    text: string;
}

interface McqMultipleContent {
    question: string;
    code?: string;
    options: Option[];
    correct_answers: string[];
}

const model = defineModel<McqMultipleContent>({ required: true });

if (!Array.isArray(model.value.options)) {
    model.value.options = [{ id: 'a', text: '' }];
}

if (!Array.isArray(model.value.correct_answers)) {
    model.value.correct_answers = [];
}

if (typeof model.value.question !== 'string') {
    model.value.question = '';
}

function addOption() {
    const nextId = String.fromCharCode(97 + model.value.options.length);
    model.value.options.push({ id: nextId, text: '' });
}

function removeOption(index: number) {
    const removed = model.value.options[index];
    model.value.options.splice(index, 1);
    model.value.correct_answers = model.value.correct_answers.filter(
        (id) => id !== removed.id,
    );
}

function toggleCorrect(optionId: string) {
    if (model.value.correct_answers.includes(optionId)) {
        model.value.correct_answers = model.value.correct_answers.filter(
            (id) => id !== optionId,
        );
    } else {
        model.value.correct_answers.push(optionId);
    }
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Pertanyaan
            </label>
            <textarea
                v-model="model.question"
                class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
                rows="2"
            />
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Code (opsional)
            </label>
            <textarea
                v-model="model.code"
                class="w-full rounded-md border border-gray-300 p-2 font-mono text-sm focus:border-gray-900 focus:outline-none"
                rows="3"
            />
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">
                    Opsi Jawaban
                </label>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addOption"
                >
                    <Plus class="h-3.5 w-3.5" />
                    Tambah
                </Button>
            </div>

            <div class="space-y-2">
                <div
                    v-for="(option, index) in model.options"
                    :key="index"
                    class="flex items-center gap-2"
                >
                    <input
                        :checked="model.correct_answers.includes(option.id)"
                        type="checkbox"
                        class="h-4 w-4"
                        @change="toggleCorrect(option.id)"
                    />
                    <span class="w-6 font-mono text-xs uppercase">
                        {{ option.id }}
                    </span>
                    <input
                        v-model="option.text"
                        class="flex-1 rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        @click="removeOption(index)"
                    >
                        <Trash2 class="h-4 w-4 text-red-600" />
                    </Button>
                </div>
            </div>
            <p class="mt-1 text-xs text-gray-500">
                Centang semua jawaban yang benar.
            </p>
        </div>
    </div>
</template>
