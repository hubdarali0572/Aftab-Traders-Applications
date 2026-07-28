<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    series: {
        type: Array,
        default: () => [],
    },
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
    color: { type: String, default: '#4f46e5' },
    height: { type: Number, default: 260 },
    formatValue: { type: Function, default: (v) => Number(v || 0).toLocaleString() },
    showLegend: { type: Boolean, default: true },
});

const hover = ref(null);

const chartSeries = computed(() => {
    if (props.series?.length) return props.series;
    return [
        {
            key: 'value',
            label: props.title || 'Value',
            color: props.color,
            data: props.values,
        },
    ];
});

const points = computed(() => {
    return (props.labels || []).map((label, i) => {
        const bars = chartSeries.value.map((s) => ({
            key: s.key,
            label: s.label,
            color: s.color,
            value: Number((s.data?.[i] ?? 0) || 0),
        }));
        return { label, bars, index: i };
    });
});

const maxValue = computed(() => {
    let max = 0;
    points.value.forEach((p) => p.bars.forEach((b) => { max = Math.max(max, b.value); }));
    return max > 0 ? max : 1;
});

const ticks = computed(() => {
    const max = maxValue.value;
    return [1, 0.75, 0.5, 0.25, 0].map((r) => ({
        ratio: r,
        label: props.formatValue(max * r),
    }));
});

const barPct = (value) => `${Math.max((Number(value || 0) / maxValue.value) * 100, value > 0 ? 4 : 0)}%`;

const seriesCount = computed(() => chartSeries.value.length);
const barMaxWidth = computed(() => (seriesCount.value > 1 ? '14px' : '22px'));
</script>

<template>
    <div class="report-chart w-full shrink-0">
        <div
            v-if="title || subtitle || (showLegend && chartSeries.length)"
            class="flex items-start justify-between gap-3 mb-3 px-1"
        >
            <div>
                <h3 v-if="title" class="text-sm font-black text-slate-800 dark:text-slate-100">{{ title }}</h3>
                <p v-if="subtitle" class="text-[11px] text-slate-400 mt-0.5">{{ subtitle }}</p>
            </div>
            <div v-if="showLegend && chartSeries.length" class="flex flex-wrap items-center justify-end gap-3">
                <span
                    v-for="s in chartSeries"
                    :key="s.key"
                    class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                >
                    <span class="w-2.5 h-2.5 rounded-sm shrink-0" :style="{ background: s.color }"></span>
                    {{ s.label }}
                </span>
            </div>
        </div>

        <div
            class="relative w-full shrink-0 overflow-hidden rounded-lg border border-slate-200/80 bg-[linear-gradient(180deg,#f8fafc_0%,#ffffff_55%)] dark:border-slate-700 dark:bg-[linear-gradient(180deg,#0f172a_0%,#1e293b_60%)]"
            :style="{ height: `${height}px` }"
        >
            <!-- Y-axis grid -->
            <div class="absolute inset-0 pl-12 pr-3 pt-5 pb-9 pointer-events-none">
                <div class="relative h-full">
                    <div
                        v-for="tick in ticks"
                        :key="tick.ratio"
                        class="absolute left-0 right-0 border-t border-dashed border-slate-200/90 dark:border-slate-600/50"
                        :style="{ bottom: `${tick.ratio * 100}%` }"
                    >
                        <span class="absolute -left-11 -translate-y-1/2 w-10 text-right text-[9px] font-bold tabular-nums text-slate-400">
                            {{ tick.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bars -->
            <div class="absolute inset-0 pl-12 pr-3 pt-5 pb-9">
                <div v-if="points.length" class="flex h-full items-end justify-between gap-1 sm:gap-1.5">
                    <div
                        v-for="point in points"
                        :key="point.index"
                        class="group relative flex h-full min-w-0 flex-1 flex-col items-center justify-end"
                        @mouseenter="hover = point.index"
                        @mouseleave="hover = null"
                    >
                        <div class="flex h-full w-full items-end justify-center gap-0.5 px-0.5">
                            <div
                                v-for="bar in point.bars"
                                :key="bar.key"
                                class="relative w-full origin-bottom rounded-t-md transition-all duration-300 ease-out"
                                :class="hover !== null && hover !== point.index ? 'opacity-35' : 'opacity-100'"
                                :style="{
                                    height: barPct(bar.value),
                                    maxWidth: barMaxWidth,
                                    background: `linear-gradient(180deg, ${bar.color} 0%, ${bar.color}dd 45%, ${bar.color}99 100%)`,
                                    boxShadow: hover === point.index ? `0 10px 20px -12px ${bar.color}` : 'none',
                                }"
                            >
                                <span
                                    v-if="hover === point.index && point.bars.length === 1"
                                    class="absolute -top-6 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] font-black text-slate-700 dark:text-slate-200"
                                >
                                    {{ formatValue(bar.value) }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="hover === point.index && point.bars.length > 1"
                            class="pointer-events-none absolute bottom-[calc(100%-4px)] z-10 rounded-md bg-slate-900 px-2.5 py-1.5 text-white shadow-lg"
                        >
                            <p class="mb-1 text-[10px] font-bold opacity-80">{{ point.label }}</p>
                            <div
                                v-for="bar in point.bars"
                                :key="`t-${bar.key}`"
                                class="flex items-center gap-2 whitespace-nowrap text-[10px] font-bold"
                            >
                                <span class="h-1.5 w-1.5 rounded-full" :style="{ background: bar.color }"></span>
                                <span class="opacity-70">{{ bar.label }}</span>
                                <span>{{ formatValue(bar.value) }}</span>
                            </div>
                        </div>

                        <span class="absolute -bottom-7 left-0 right-0 truncate px-0.5 text-center text-[9px] font-bold text-slate-400 sm:text-[10px]">
                            {{ point.label }}
                        </span>
                    </div>
                </div>
                <div v-else class="flex h-full items-center justify-center text-sm font-medium text-slate-400">
                    No chart data available
                </div>
            </div>
        </div>
    </div>
</template>
