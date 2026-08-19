<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import adminRoutes from '@/routes/admin';
import adminBlockRoutes from '@/routes/admin/blocks';

interface Lesson {
    id: number;
    title: string;
}

const props = defineProps<{
    lesson: Lesson;
    nextSortOrder: number;
}>();

const blockTypes: { value: string; label: string; description: string }[] = [
    {
        value: 'TEXT',
        label: 'Text (Markdown)',
        description: 'Materi teks dengan format markdown.',
    },
    {
        value: 'CODE_EXAMPLE',
        label: 'Code Example',
        description: 'Contoh kode read-only dengan syntax highlighting.',
    },
    {
        value: 'HINT',
        label: 'Hint',
        description: 'Petunjuk collapsible untuk membantu user.',
    },
    {
        value: 'MCQ_SINGLE',
        label: 'MCQ Single Answer',
        description: 'Pertanyaan pilihan ganda satu jawaban benar.',
    },
    {
        value: 'MCQ_MULTIPLE',
        label: 'MCQ Multiple Answer',
        description: 'Pertanyaan pilihan ganda multi-jawaban benar.',
    },
    {
        value: 'CODE_FILL',
        label: 'Code Fill',
        description: 'Lengkapi bagian kosong pada kode.',
    },
    {
        value: 'CODE_REORDER',
        label: 'Code Reorder',
        description: 'Susun ulang baris kode ke urutan benar.',
    },
    {
        value: 'CODE_CHALLENGE',
        label: 'Code Challenge',
        description: 'Soal coding dengan testcase dan eksekusi Pyodide.',
    },
];

const defaultContent: Record<string, Record<string, unknown>> = {
    TEXT: { text: '' },
    CODE_EXAMPLE: { language: 'python', code: '' },
    HINT: { title: 'Petunjuk', text: '' },
    MCQ_SINGLE: {
        question: '',
        options: [{ id: 'a', text: '' }],
        correct_answer: 'a',
    },
    MCQ_MULTIPLE: {
        question: '',
        options: [{ id: 'a', text: '' }],
        correct_answers: ['a'],
    },
    CODE_FILL: { code_template: '', blanks: [{ id: 'A', answer: '' }] },
    CODE_REORDER: { lines: [''], correct_order: [0] },
    CODE_CHALLENGE: {
        prompt: '',
        starter_code: '',
        testcases: [],
    },
};

const form = reactive({
    type: 'TEXT',
    content: defaultContent.TEXT,
    sort_order: props.nextSortOrder,
});

const errors = reactive<Record<string, string>>({});

function onTypeChange() {
    form.content = defaultContent[form.type] ?? {};
}

function submit() {
    router.post(adminBlockRoutes.store.url({ lesson: props.lesson.id }), form, {
        onError: (serverErrors) => {
            Object.assign(errors, serverErrors);
        },
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminRoutes.dashboard() },
            { title: 'Blocks', href: '#' },
            { title: 'Create', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Admin - Block Baru" />

    <div class="mx-auto max-w-2xl p-4">
        <Link
            :href="adminBlockRoutes.index.url({ lesson: lesson.id })"
            class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900"
        >
            <ArrowLeft class="h-3.5 w-3.5" />
            Kembali ke Blocks
        </Link>

        <h1 class="mb-6 text-2xl font-bold tracking-tight">Block Baru</h1>

        <Card>
            <CardHeader>
                <CardTitle>Pilih Tipe Block</CardTitle>
            </CardHeader>

            <CardContent>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <Label for="type">Tipe Block</Label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="w-full rounded-md border border-gray-300 p-2 text-sm focus:border-gray-900 focus:outline-none"
                            @change="onTypeChange"
                        >
                            <option
                                v-for="bt in blockTypes"
                                :key="bt.value"
                                :value="bt.value"
                            >
                                {{ bt.label }}
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            {{
                                blockTypes.find((bt) => bt.value === form.type)
                                    ?.description
                            }}
                        </p>
                    </div>

                    <div>
                        <Label for="sort_order">Sort Order</Label>
                        <Input
                            id="sort_order"
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            required
                        />
                    </div>

                    <p class="rounded-lg bg-blue-50 p-3 text-xs text-blue-700">
                        Setelah simpan, kamu akan diarahkan ke editor untuk
                        melengkapi konten block sesuai tipe yang dipilih.
                    </p>

                    <div class="flex gap-2 pt-4">
                        <Button type="submit">Buat Block</Button>

                        <Button as-child variant="outline">
                            <Link
                                :href="
                                    adminBlockRoutes.index.url({
                                        lesson: lesson.id,
                                    })
                                "
                            >
                                Batal
                            </Link>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
