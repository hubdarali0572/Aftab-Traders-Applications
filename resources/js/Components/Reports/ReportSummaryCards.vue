<script setup>
defineProps({
    cards: { type: Array, default: () => [] },
    title: { type: String, default: 'Report Summary' },
    subtitle: { type: String, default: '' },
});

const palettes = [
    { tone: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-500/10', border: 'border-indigo-200 dark:border-indigo-800' },
    { tone: 'text-sky-700 dark:text-sky-300', bg: 'bg-sky-50 dark:bg-sky-500/10', border: 'border-sky-200 dark:border-sky-800' },
    { tone: 'text-violet-700 dark:text-violet-300', bg: 'bg-violet-50 dark:bg-violet-500/10', border: 'border-violet-200 dark:border-violet-800' },
    { tone: 'text-emerald-700 dark:text-emerald-300', bg: 'bg-emerald-50 dark:bg-emerald-500/10', border: 'border-emerald-200 dark:border-emerald-800' },
    { tone: 'text-amber-700 dark:text-amber-300', bg: 'bg-amber-50 dark:bg-amber-500/10', border: 'border-amber-200 dark:border-amber-800' },
    { tone: 'text-rose-700 dark:text-rose-300', bg: 'bg-rose-50 dark:bg-rose-500/10', border: 'border-rose-200 dark:border-rose-800' },
];

const defaultIcon = 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z';

const paletteFor = (card, index) => palettes[index % palettes.length];
</script>

<template>
    <div class="theme-table-card mb-6 overflow-hidden print:hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 via-violet-50/50 to-white dark:from-indigo-950/40 dark:via-violet-950/20 dark:to-slate-800">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t(title) }}</h3>
            <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">{{ $t(subtitle) }}</p>
        </div>
        <div class="p-6 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            <div
                v-for="(card, index) in cards"
                :key="card.title"
                class="flex items-start gap-3 p-4 rounded-xl border transition-all hover:shadow-md"
                :class="[
                    card.highlight || card.tone?.includes('emerald') || card.tone?.includes('rose')
                        ? (card.tone?.includes('rose') ? 'border-rose-200 dark:border-rose-800 bg-rose-50/40 dark:bg-rose-950/20' : 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/40 dark:bg-indigo-950/20')
                        : 'border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50',
                ]"
            >
                <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                    :class="card.bg || paletteFor(card, index).bg"
                >
                    <svg
                        class="w-5 h-5"
                        :class="card.tone || paletteFor(card, index).tone"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon || defaultIcon" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400 leading-tight">{{ $t(card.title) }}</p>
                    <p class="text-base font-black mt-1 truncate" :class="card.tone || 'text-slate-800 dark:text-slate-100'">{{ card.value }}</p>
                    <p v-if="card.sub" class="text-[10px] text-slate-400 mt-0.5 truncate">{{ card.sub }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
