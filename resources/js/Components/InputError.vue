<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-indigo-600 dark:text-indigo-300">
            {{ message }}
        </p>
    </div>
</template>
