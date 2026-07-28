<script setup>
defineProps({
    cards: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div
            v-for="card in cards"
            :key="card.title"
            class="bg-white p-4 rounded-sm shadow-sm border border-slate-100 dark:bg-slate-800 dark:border-slate-700"
        >
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.12em] dark:text-slate-500">{{ $t(card.title) }}</p>
            <p class="text-xl font-black mt-1.5 text-slate-800 dark:text-slate-100 truncate" :class="card.tone">{{ card.value }}</p>
        </div>
    </div>
</template>
