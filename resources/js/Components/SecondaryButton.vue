<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm transition duration-150 ease-in-out hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:border-indigo-400/30 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
    >
        <slot />
    </button>
</template>
