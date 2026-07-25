<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const page = usePage();

const laravelVersion = computed(() => page.props.laravelVersion);
const phpVersion = computed(() => page.props.phpVersion);
const currentYear = computed(() => new Date().getFullYear());

const navItems = [
    { label: "Home", href: route("publicSite.home") },
    { label: "Features", href: `${route("publicSite.home")}#features` },
    { label: "About", href: route("publicSite.about") },
    { label: "Tech Stack", href: `${route("publicSite.home")}#stack` },
    { label: "Security", href: `${route("publicSite.home")}#security` },
];

const isAnchor = (href) => href.startsWith("#") || href.includes("#");
</script>

<template>
    <footer
        class="w-full max-w-full overflow-x-hidden border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950"
    >
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8 lg:py-14">
            <div
                class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between md:gap-10 lg:gap-16"
            >
                <div class="flex max-w-md flex-col gap-3 sm:gap-4">
                    <Link
                        :href="route('publicSite.home')"
                        class="inline-flex items-center gap-2.5 sm:gap-3"
                        aria-label="Unified Media home"
                    >
                        <ApplicationLogo
                            class="h-6 w-6 shrink-0 fill-slate-400 sm:h-7 sm:w-7 dark:fill-slate-500"
                            aria-hidden="true"
                        />
                        <span
                            class="text-sm font-semibold tracking-tight text-slate-700 sm:text-base dark:text-slate-200"
                        >
                            Unified Media
                        </span>
                    </Link>
                    <p
                        class="text-sm leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        Precision-Engineered Governance for Unified Digital
                        Media
                    </p>
                </div>

                <nav
                    class="w-full md:max-w-xs lg:max-w-none lg:flex-1"
                    aria-label="Footer"
                >
                    <p
                        class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500"
                    >
                        Explore
                    </p>
                    <ul
                        class="grid grid-cols-2 gap-x-4 gap-y-1 sm:grid-cols-3 sm:gap-x-6 md:grid-cols-2 lg:grid-cols-5 lg:gap-x-4"
                    >
                        <li v-for="item in navItems" :key="item.label">
                            <a
                                v-if="isAnchor(item.href)"
                                :href="item.href"
                                class="inline-flex min-h-12 items-center text-sm text-slate-600 transition-colors hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400"
                            >
                                {{ item.label }}
                            </a>
                            <Link
                                v-else
                                :href="item.href"
                                class="inline-flex min-h-12 items-center text-sm text-slate-600 transition-colors hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400"
                            >
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>

            <div
                class="mt-8 flex flex-col items-start justify-between gap-3 border-t border-slate-200 pt-6 sm:mt-10 sm:flex-row sm:items-center dark:border-slate-800"
            >
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    &copy; {{ currentYear }} Unified Media. All rights reserved.
                </p>
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    Laravel v{{ laravelVersion }} &middot; PHP v{{ phpVersion }}
                </p>
            </div>
        </div>
    </footer>
</template>
