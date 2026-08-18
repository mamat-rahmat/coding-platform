<script setup lang="ts">
defineProps<{
    content: {
        text?: string;
    };
}>();
</script>

<template>
    <p class="leading-8 text-gray-700">
        {{ content.text }}
    </p>
</template>