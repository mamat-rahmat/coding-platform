<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import courseRoutes from '@/routes/courses';

interface LessonBlock {
    id: number;
    type: string;
    content: {
        text?: string;
        language?: string;
        code?: string;
    };
    sort_order: number;
}

interface Lesson {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    blocks: LessonBlock[];
    module: {
        id: number;
        title: string;
        course: {
            id: number;
            title: string;
            slug: string;
        };
    };
}

defineProps<{
    lesson: Lesson;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-4xl px-6 py-10">

            <!-- Breadcrumb -->
            <div class="mb-8 text-sm text-gray-500">
                <Link
                    :href="courseRoutes.show.url(lesson.module.course.slug)"
                    class="hover:text-gray-900"
                >
                    {{ lesson.module.course.title }}
                </Link>

                <span class="mx-2">/</span>

                <span>{{ lesson.module.title }}</span>
            </div>

            <!-- Lesson Header -->
            <header class="mb-10">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    {{ lesson.title }}
                </h1>

                <p
                    v-if="lesson.description"
                    class="mt-3 text-lg text-gray-600"
                >
                    {{ lesson.description }}
                </p>
            </header>

            <!-- Lesson Blocks -->
            <main class="space-y-8">
                <section
                    v-for="block in lesson.blocks"
                    :key="block.id"
                    class="rounded-xl border bg-white p-6 shadow-sm"
                >
                    <!-- TEXT -->
                    <template v-if="block.type === 'TEXT'">
                        <p class="leading-8 text-gray-700">
                            {{ block.content.text }}
                        </p>
                    </template>

                    <!-- CODE EXAMPLE -->
                    <template v-else-if="block.type === 'CODE_EXAMPLE'">
                        <div class="overflow-hidden rounded-lg bg-gray-900">
                            <div
                                class="border-b border-gray-700 px-4 py-2 text-xs text-gray-400"
                            >
                                {{ block.content.language }}
                            </div>

                            <pre
                                class="overflow-x-auto p-5 text-sm leading-6 text-gray-100"
                            ><code>{{ block.content.code }}</code></pre>
                        </div>
                    </template>

                    <!-- Unknown block -->
                    <template v-else>
                        <p class="text-sm text-gray-500">
                            Block type "{{ block.type }}" belum didukung.
                        </p>
                    </template>
                </section>
            </main>

            <!-- Navigation -->
            <div class="mt-10 flex justify-end border-t pt-6">
                <button
                    type="button"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-700"
                >
                    Lanjut →
                </button>
            </div>
        </div>
    </div>
</template>