<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
    mobile: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["navigate"]);

const isAnchor = computed(
    () => props.href.startsWith("#") || props.href.includes("#"),
);

const classes = computed(() => {
    if (props.mobile) {
        return [
            "block w-full rounded-lg px-4 py-3 text-base font-medium transition-colors",
            props.active
                ? "bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                : "text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/80",
        ];
    }

    return [
        "inline-flex items-center whitespace-nowrap text-sm font-medium transition-colors",
        props.active
            ? "text-indigo-600 dark:text-indigo-400"
            : "text-slate-600 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400",
    ];
});
</script>

<template>
    <a
        v-if="isAnchor"
        :href="href"
        :class="classes"
        @click="emit('navigate')"
    >
        <slot />
    </a>
    <Link v-else :href="href" :class="classes" @click="emit('navigate')">
        <slot />
    </Link>
</template>
