<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    items: { type: Array, default: () => [] },
    color: { type: String, default: '#4f46e5' },
    height: { type: Number, default: 260 },
    formatValue: {
        type: Function,
        default: (v) =>
            `$${Number(v || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })}`,
    },
    emptyText: { type: String, default: 'No distribution data' },
});

const max = computed(() => Math.max(...props.items.map((i) => Number(i.amount || 0)), 1));
const total = computed(() => props.items.reduce((s, i) => s + Number(i.amount || 0), 0));
</script>

<template>
    <div class="w-full shrink-0">
        <div v-if="title" class="mb-3 px-1">
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100">{{ title }}</h3>
        </div>
        <div
            class="shrink-0 overflow-auto rounded-lg border border-slate-200/80 bg-[linear-gradient(180deg,#f8fafc_0%,#ffffff_55%)] dark:border-slate-700 dark:bg-[linear-gradient(180deg,#0f172a_0%,#1e293b_60%)]"
            :style="{ height: `${height}px` }"
        >
            <div v-if="items.length" class="space-y-3.5 p-4">
                <div v-for="(item, idx) in items" :key="item.name || idx" class="group">
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <span
                                class="h-2 w-2 shrink-0 rounded-full"
                                :style="{ background: color, opacity: Math.max(0.45, 1 - idx * 0.08) }"
                            ></span>
                            <span class="truncate text-xs font-bold text-slate-700 dark:text-slate-200">{{ item.name }}</span>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-xs font-black tabular-nums text-slate-800 dark:text-slate-100">
                                {{ formatValue(item.amount) }}
                            </span>
                            <span class="ml-2 text-[10px] font-bold tabular-nums text-slate-400">
                                {{ total > 0 ? ((Number(item.amount || 0) / total) * 100).toFixed(1) : 0 }}%
                            </span>
                        </div>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/80">
                        <div
                            class="h-full rounded-full transition-all duration-500 ease-out"
                            :style="{
                                width: `${(Number(item.amount || 0) / max) * 100}%`,
                                background: `linear-gradient(90deg, ${color}, ${color}aa)`,
                            }"
                        ></div>
                    </div>
                </div>
            </div>
            <div v-else class="flex h-full items-center justify-center text-sm font-medium text-slate-400">
                {{ emptyText }}
            </div>
        </div>
    </div>
</template>
