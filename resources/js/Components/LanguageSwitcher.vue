<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useLocale } from '@/i18n';

const { locale, setLocale, t } = useLocale();
const open = ref(false);
const root = ref(null);

const options = [
    { code: 'en', label: 'English', short: 'EN' },
    { code: 'ur', label: 'Urdu', short: 'UR' },
];

const current = computed(() => options.find((o) => o.code === locale.value) || options[0]);

const select = (code) => {
    setLocale(code);
    open.value = false;
};

const onDocClick = (e) => {
    if (root.value && !root.value.contains(e.target)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('click', onDocClick));
onUnmounted(() => document.removeEventListener('click', onDocClick));
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-slate-600 transition-colors hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600"
            :aria-label="t('Language')"
            :title="t('Language')"
            :aria-expanded="open"
            @click.stop="open = !open"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M12 3c2.5 2.7 3.8 5.8 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.8-3.8-9s1.3-6.3 3.8-9z" />
            </svg>
            <span class="text-[10px] font-bold uppercase tracking-wider">{{ current.short }}</span>
            <svg class="hidden h-3.5 w-3.5 sm:block" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute end-0 z-50 mt-2 w-40 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-slate-600 dark:bg-slate-800"
            role="listbox"
            :aria-label="t('Language')"
        >
            <button
                v-for="opt in options"
                :key="opt.code"
                type="button"
                role="option"
                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm font-semibold transition-colors"
                :class="
                    locale === opt.code
                        ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300'
                        : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700'
                "
                :aria-selected="locale === opt.code"
                @click="select(opt.code)"
            >
                <span>{{ t(opt.label) }}</span>
                <span class="text-[10px] font-bold uppercase tracking-wider opacity-60">{{ opt.short }}</span>
            </button>
        </div>
    </div>
</template>
