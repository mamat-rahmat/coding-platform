<script setup lang="ts">
interface TextContent {
    text: string;
}

defineProps<{
    content: TextContent;
}>();
</script>

<template>
    <p class="leading-8 text-gray-700">
        {{ content.text }}
    </p>
</template>