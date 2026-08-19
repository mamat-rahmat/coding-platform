<script setup lang="ts">
import { marked } from 'marked';
import { computed } from 'vue';

const props = defineProps<{
    content: string;
}>();

marked.setOptions({
    breaks: true,
    gfm: true,
});

function sanitize(html: string): string {
    return html
        .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
        .replace(/\son\w+\s*=\s*"[^"]*"/gi, '')
        .replace(/\son\w+\s*=\s*'[^']*'/gi, '')
        .replace(/\son\w+\s*=\s*[^\s>]+/gi, '')
        .replace(
            /(href|src)\s*=\s*("javascript:[^"]*"|'javascript:[^']*')/gi,
            '',
        );
}

const html = computed(() => sanitize(marked.parse(props.content) as string));
</script>

<template>
    <div class="markdown-body" v-html="html" />
</template>

<style scoped>
.markdown-body :deep(h1) {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 1rem 0 0.5rem;
}

.markdown-body :deep(h2) {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 1rem 0 0.5rem;
}

.markdown-body :deep(h3) {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0.75rem 0 0.5rem;
}

.markdown-body :deep(p) {
    margin: 0.5rem 0;
    line-height: 1.75;
}

.markdown-body :deep(ul),
.markdown-body :deep(ol) {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}

.markdown-body :deep(ul) {
    list-style: disc;
}

.markdown-body :deep(ol) {
    list-style: decimal;
}

.markdown-body :deep(li) {
    margin: 0.25rem 0;
    line-height: 1.75;
}

.markdown-body :deep(code) {
    background-color: #f3f4f6;
    border-radius: 0.25rem;
    padding: 0.125rem 0.25rem;
    font-size: 0.875em;
    font-family:
        ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.markdown-body :deep(pre) {
    background-color: #1f2937;
    color: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem;
    overflow-x: auto;
    margin: 0.75rem 0;
}

.markdown-body :deep(pre code) {
    background-color: transparent;
    padding: 0;
    color: inherit;
}

.markdown-body :deep(blockquote) {
    border-left: 4px solid #d1d5db;
    padding-left: 1rem;
    margin: 0.75rem 0;
    color: #6b7280;
    font-style: italic;
}

.markdown-body :deep(a) {
    color: #2563eb;
    text-decoration: underline;
}

.markdown-body :deep(a:hover) {
    color: #1d4ed8;
}

.markdown-body :deep(table) {
    border-collapse: collapse;
    width: 100%;
    margin: 0.75rem 0;
}

.markdown-body :deep(th),
.markdown-body :deep(td) {
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    text-align: left;
}

.markdown-body :deep(th) {
    background-color: #f9fafb;
    font-weight: 600;
}
</style>
