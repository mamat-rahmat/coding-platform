<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';

interface Blank {
    id: string;
    answer: string;
    alternatives?: string[];
}

interface CodeFillContent {
    code_template: string;
    blanks: Blank[];
}

const model = defineModel<CodeFillContent>({ required: true });

if (typeof model.value.code_template !== 'string') {
    model.value.code_template = '';
}

if (!Array.isArray(model.value.blanks)) {
    model.value.blanks = [];
}

function addBlank() {
    const nextId = String.fromCharCode(65 + model.value.blanks.length);
    model.value.blanks.push({ id: nextId, answer: '' });
}

function removeBlank(index: number) {
    model.value.blanks.splice(index, 1);
}

function placeholder(id: string): string {
    return `{{ ${id} }}`;
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">
                Code Template
            </label>
            <p class="mb-2 text-xs text-gray-500">
                Gunakan placeholder <code v-pre>{{ A }}</code
                >, <code v-pre>{{ B }}</code
                >, dst. untuk blank.
            </p>
            <textarea
                v-model="model.code_template"
                class="w-full rounded-md border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
                rows="8"
            />
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">
                    Blanks
                </label>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addBlank"
                >
                    <Plus class="h-3.5 w-3.5" />
                    Tambah Blank
                </Button>
            </div>

            <div class="space-y-2">
                <div
                    v-for="(blank, index) in model.blanks"
                    :key="index"
                    class="flex items-center gap-2"
                >
                    <span class="w-8 font-mono text-xs uppercase">
                        {{ placeholder(blank.id) }}
                    </span>
                    <input
                        v-model="blank.answer"
                        class="flex-1 rounded-md border border-gray-300 p-2 font-mono text-sm focus:border-gray-900 focus:outline-none"
                        placeholder="Jawaban benar"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        @click="removeBlank(index)"
                    >
                        <Trash2 class="h-4 w-4 text-red-600" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
