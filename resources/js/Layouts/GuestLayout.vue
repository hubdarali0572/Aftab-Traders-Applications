<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
import { useDarkMode } from "@/composables/useDarkMode";

const { isDark, toggleDarkMode } = useDarkMode();

const highlights = [
    "Role-based access control",
    "Media library & image processing",
    "Activity logs & audit trails",
];
</script>

<template>
    <div
        class="theme-app-bg flex min-h-screen w-full max-w-[100vw] flex-col overflow-x-hidden lg:flex-row"
    >
        <!-- Branding panel (desktop only) -->
        <div
            class="relative hidden w-full overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-indigo-900 text-white lg:flex lg:w-[45%] xl:w-1/2"
        >
            <div
                class="absolute -right-16 -top-16 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"
            />
            <div
                class="absolute -left-10 bottom-20 h-48 w-48 rounded-full bg-indigo-500/15 blur-2xl"
            />

            <div
                class="relative z-10 flex w-full flex-col justify-between p-10 xl:p-16"
            >
                <Link href="/" class="flex w-fit items-center gap-3">
                    <ApplicationLogo class="h-20 w-20 shrink-0 fill-indigo-400 rounded-3xl" />
                   
                </Link>

                <div class="max-w-md">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300"
                    >
                        Platform
                    </p>
                    <h1
                        class="mt-3 text-3xl font-extrabold leading-tight tracking-tight xl:text-4xl"
                    >
                        Precision-Engineered
                        <span class="text-indigo-300">Governance</span>
                        for Digital Media
                    </h1>
                    <p class="mt-4 text-sm leading-relaxed text-white/70">
                        Secure user management, rich media handling, and
                        complete operational transparency — all in one cohesive
                        platform.
                    </p>

                    <ul class="mt-8 space-y-3">
                        <li
                            v-for="item in highlights"
                            :key="item"
                            class="flex items-center gap-3 text-sm text-white/80"
                        >
                            <span
                                class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-indigo-500/30"
                            >
                                <svg
                                    class="h-3 w-3 text-indigo-300"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                            </span>
                            {{ item }}
                        </li>
                    </ul>
                </div>

                <p class="text-xs text-white/40">
                    &copy; {{ new Date().getFullYear() }} AFTAB TRADERS PLATFORM
                </p>
            </div>
        </div>

        <!-- Form panel -->
        <div
            class="flex min-h-screen w-full min-w-0 flex-1 flex-col overflow-x-hidden"
        >
            <header
                class="flex shrink-0 items-center justify-between gap-2 px-4 py-4 sm:px-6 sm:py-5 lg:px-10"
            >
                <Link
                    href="/"
                    class="flex min-w-0 items-center gap-2 lg:invisible lg:pointer-events-none"
                    aria-label="Aftab Traders home"
                >
                    <ApplicationLogo
                        class="h-7 w-7 shrink-0 fill-indigo-600 sm:h-8 sm:w-8 dark:fill-indigo-400"
                    />
                    <span
                        class="truncate text-sm font-bold text-slate-800 dark:text-white"
                        >Aftab Traders</span
                    >
                </Link>

                <div class="flex shrink-0 items-center gap-1 sm:gap-2">

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-200/60 dark:text-slate-400 dark:hover:bg-slate-800"
                        :aria-label="
                            isDark
                                ? 'Switch to light mode'
                                : 'Switch to dark mode'
                        "
                        @click="toggleDarkMode"
                    >
                        <svg
                            v-if="isDark"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                            />
                        </svg>
                    </button>
                </div>
            </header>

            <main
                class="flex flex-1 items-start justify-center px-4 pb-8 pt-2 sm:items-center sm:px-6 sm:pb-10 lg:px-10"
            >
                <div class="w-full max-w-md min-w-0">
                    <div
                        class="rounded-2xl bg-white p-5 shadow-xl shadow-slate-200/50 ring-1 ring-slate-200 sm:p-8 dark:bg-slate-900 dark:shadow-none dark:ring-slate-800"
                    >
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
