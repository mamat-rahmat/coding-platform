<script setup lang="ts">
import { Plus, Trash2 } from '@lucide/vue';
import Markdown from '@/components/Markdown.vue';
import { Button } from '@/components/ui/button';

interface Blank {
    id: string;
    answer: string;
    alternatives?: string[];
}

interface CodeFillContent {
    code_template: string;
    blanks: Blank[];
    options?: string[];
    markdown?: string;
}

const model = defineModel<CodeFillContent>({ required: true });

if (typeof model.value.code_template !== 'string') {
    model.value.code_template = '';
}

if (!Array.isArray(model.value.blanks)) {
    model.value.blanks = [];
}

if (typeof model.value.markdown !== 'string') {
    model.value.markdown = '';
}

if (!Array.isArray(model.value.options)) {
    model.value.options = [];
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

function addExtraOption() {
    model.value.options!.push('');
}

function removeExtraOption(index: number) {
    model.value.options!.splice(index, 1);
}
</script>

<template>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    Judul / Penjelasan (Markdown)
                </label>
                <textarea
                    v-model="model.markdown"
                    class="h-32 w-full rounded-md border border-gray-300 p-3 font-mono text-sm focus:border-gray-900 focus:outline-none"
                    rows="3"
                    placeholder="Apa yang diminta untuk dilengkapi?"
                />
            </div>

            <div class="flex flex-col">
                <span class="mb-1 block text-sm font-medium text-gray-700">
                    Preview
                </span>
                <div
                    class="h-32 flex-1 overflow-y-auto rounded-md border border-dashed border-gray-300 bg-gray-50 p-3"
                >
                    <p
                        v-if="(model.markdown || '').trim() === ''"
                        class="text-sm text-gray-400"
                    >
                        Preview akan tampil di sini.
                    </p>
                    <Markdown v-else :content="model.markdown ?? ''" />
                </div>
            </div>
        </div>

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
                    <span class="font-mono text-xs whitespace-nowrap uppercase">
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

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">
                    Opsi Tambahan (Distractor)
                </label>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addExtraOption"
                >
                    <Plus class="h-3.5 w-3.5" />
                    Tambah Opsi
                </Button>
            </div>

            <p class="mb-2 text-xs text-gray-500">
                Opsi tambahan ditampilkan sebagai pilihan yang salah. Jawaban
                benar dari blanks selalu ditampilkan.
            </p>

            <div
                v-if="(model.options ?? []).length === 0"
                class="rounded-md border border-dashed border-gray-300 p-3 text-center text-xs text-gray-400"
            >
                Tidak ada opsi tambahan.
            </div>

            <div class="space-y-2">
                <div
                    v-for="(opt, index) in model.options"
                    :key="index"
                    class="flex items-center gap-2"
                >
                    <input
                        v-model="model.options![index]"
                        class="flex-1 rounded-md border border-gray-300 p-2 font-mono text-sm focus:border-gray-900 focus:outline-none"
                        placeholder="Teks opsi salah"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        @click="removeExtraOption(index)"
                    >
                        <Trash2 class="h-4 w-4 text-red-600" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
