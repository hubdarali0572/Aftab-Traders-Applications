<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import PublicSiteLayout from "@/Layouts/PublicSite/PublicSiteLayout.vue";
import { computed } from "vue";

const page = usePage();
const laravelVersion = computed(() => page.props.laravelVersion);
const phpVersion = computed(() => page.props.phpVersion);

const seoTitle = "Home";
const seoDescription =
    "Precision-engineered governance for unified digital media. Secure user management, media libraries, activity logs, reCAPTCHA protection, and automated backups — built with Laravel, Inertia, and Vue 3.";
const canonicalUrl = computed(() => route("publicSite.home"));

const pillars = [
    {
        label: "Governance",
        description:
            "Role-based access and full audit trails for every action.",
    },
    {
        label: "Media",
        description:
            "Centralized digital assets with advanced image processing.",
    },
    {
        label: "Security",
        description: "Bot protection and automated backups for peace of mind.",
    },
];

const features = [
    {
        title: "Laravel 12 + Inertia + Vue 3",
        category: "Core Architecture",
        description:
            "A high-performance single-page application stack that combines Laravel's robust backend with Vue 3 reactivity and Inertia.js for seamless, server-driven navigation without the complexity of a separate API layer.",
        icon: "M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4",
        accent: "indigo",
    },
    {
        title: "Spatie Media Library",
        category: "Asset Management",
        description:
            "Upload, organize, and serve digital media through a unified library. Attach files to any model, generate conversions, and maintain a single source of truth for all visual assets across the platform.",
        icon: "M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z",
        accent: "indigo",
    },
    {
        title: "Image Intervention",
        category: "Image Processing",
        description:
            "Resize, crop, optimize, and transform images on the fly. Ensure consistent output quality and performance for thumbnails, avatars, and media previews without relying on external services.",
        icon: "M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15",
        accent: "indigo",
    },
    {
        title: "Spatie Permissions",
        category: "Access Control",
        description:
            "Granular Role-Based Access Control (RBAC) with roles, permissions, and middleware guards. Define who can view, create, edit, or delete resources at every level of the application.",
        icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
        accent: "indigo",
    },
    {
        title: "Spatie Activity Logs",
        category: "Audit & Transparency",
        description:
            "Every meaningful change is recorded automatically. Track who did what, when, and on which resource — giving administrators complete visibility and accountability across the system.",
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
        accent: "indigo",
    },
    {
        title: "Google reCAPTCHA",
        category: "Security Layer",
        description:
            "Protect authentication and public forms from automated abuse. reCAPTCHA verification is enforced server-side, blocking bots while keeping the experience frictionless for real users.",
        icon: "M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z",
        accent: "indigo",
    },
    {
        title: "Automated Database Backups",
        category: "Data Integrity",
        description:
            "Scheduled daily database backups run automatically via Laravel's task scheduler. Your data is preserved on a consistent cadence, ensuring recoverability and long-term application integrity.",
        icon: "M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4",
        accent: "indigo",
    },
];

const accentClasses = {
    indigo: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-400",
        ring: "group-hover:ring-indigo-200 dark:group-hover:ring-indigo-500/30",
    },
    violet: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
        ring: "group-hover:ring-indigo-200 dark:group-hover:ring-indigo-500/30",
    },
    sky: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
        ring: "group-hover:ring-indigo-200 dark:group-hover:ring-indigo-500/30",
    },
    emerald: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
        ring: "group-hover:ring-indigo-200 dark:group-hover:ring-indigo-500/30",
    },
    amber: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
        ring: "group-hover:ring-indigo-200 dark:group-hover:ring-indigo-500/30",
    },
    rose: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
        ring: "group-hover:ring-indigo-200 dark:group-hover:ring-indigo-500/30",
    },
    cyan: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
        ring: "group-hover:ring-indigo-200 dark:group-hover:ring-indigo-500/30",
    },
};

</script>

<template>
    <PublicSiteLayout>
        <Head :title="seoTitle">
            <meta
                head-key="description"
                name="description"
                :content="seoDescription"
            />
            <meta
                head-key="robots"
                name="robots"
                content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1"
            />
            <link head-key="canonical" rel="canonical" :href="canonicalUrl" />
            <meta head-key="og:type" property="og:type" content="website" />
            <meta
                head-key="og:title"
                property="og:title"
                content="Home - Unified Media"
            />
            <meta
                head-key="og:description"
                property="og:description"
                :content="seoDescription"
            />
            <meta
                head-key="og:url"
                property="og:url"
                :content="canonicalUrl"
            />
            <meta
                head-key="twitter:card"
                name="twitter:card"
                content="summary_large_image"
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content="Home - Unified Media"
            />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                :content="seoDescription"
            />
        </Head>

        <!-- Hero -->
        <section
            class="relative isolate pt-16 pb-20 lg:pt-20 lg:pb-28 overflow-hidden"
        >
            <div
                class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl"
                aria-hidden="true"
            >
                <div
                    class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-indigo-500 to-slate-300 opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
                ></div>
            </div>
            <div
                class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl"
                aria-hidden="true"
            >
                <div
                    class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-indigo-500 to-slate-300 opacity-15 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
                ></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center" data-aos="fade-up">
                    <div class="mb-8 flex flex-wrap justify-center gap-3">
                        <span
                            class="rounded-full px-4 py-1.5 text-xs font-semibold ring-1 ring-slate-900/10 bg-white/70 backdrop-blur dark:ring-white/10 dark:bg-slate-900/70"
                        >
                            <span class="text-indigo-600 dark:text-indigo-400"
                                >Laravel v{{ laravelVersion }}</span
                            >
                            + Inertia + Vue 3
                        </span>
                        <span
                            class="rounded-full px-4 py-1.5 text-xs font-semibold ring-1 ring-slate-900/10 bg-white/70 backdrop-blur dark:ring-white/10 dark:bg-slate-900/70"
                        >
                            PHP v{{ phpVersion }}
                        </span>
                    </div>

                    <h1
                        class="text-3xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl xl:text-7xl"
                    >
                        Precision-Engineered
                        <span
                            class="block mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-600 bg-clip-text text-transparent"
                        >
                            Governance
                        </span>
                        for Unified Digital Media
                    </h1>

                    <p
                        class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:mt-6 sm:text-lg sm:leading-8 dark:text-slate-300"
                    >
                        A production-ready starter ecosystem built for teams who
                        need secure user management, rich media handling, and
                        complete operational transparency — all in one cohesive
                        platform.
                    </p>
                </div>

                <!-- Pillars -->
                <div
                    class="mx-auto mt-20 grid max-w-5xl grid-cols-1 gap-6 sm:grid-cols-3"
                >
                    <div
                        v-for="(pillar, index) in pillars"
                        :key="pillar.label"
                        data-aos="fade-up"
                        :data-aos-delay="index * 100"
                        class="rounded-2xl bg-white p-6 text-center ring-1 ring-slate-200 shadow-sm dark:bg-slate-900/60 dark:ring-slate-800"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400"
                        >
                            {{ pillar.label }}
                        </p>
                        <p
                            class="mt-2 text-sm text-slate-600 leading-relaxed dark:text-slate-400"
                        >
                            {{ pillar.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section
            id="features"
            class="py-20 lg:py-28 bg-white dark:bg-slate-900/40"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center" data-aos="fade-up">
                    <p
                        class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400"
                    >
                        Platform Capabilities
                    </p>
                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        Everything defined. Nothing left ambiguous.
                    </h2>
                    <p
                        class="mt-4 text-slate-600 leading-relaxed dark:text-slate-400"
                    >
                        Each module in this starter is purpose-built and fully
                        integrated — from the SPA foundation to security
                        hardening and data protection.
                    </p>
                </div>

                <div
                    class="mx-auto mt-16 grid max-w-6xl grid-cols-1 gap-6 md:grid-cols-2 lg:gap-8"
                >
                    <article
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        data-aos="fade-up"
                        :data-aos-delay="(index % 2) * 80"
                        class="group relative rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:ring-slate-300 sm:p-8 dark:bg-slate-900 dark:ring-slate-800 dark:hover:ring-slate-700"
                        :class="accentClasses[feature.accent].ring"
                    >
                            <div class="flex items-start gap-3 sm:gap-5">
                            <div
                                class="flex-shrink-0 rounded-xl p-3 transition-all duration-300"
                                :class="accentClasses[feature.accent].icon"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        :d="feature.icon"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500"
                                >
                                    {{ feature.category }}
                                </p>
                                <h3
                                    class="mt-1 text-lg font-bold text-slate-900 dark:text-white"
                                >
                                    {{ feature.title }}
                                </h3>
                                <p
                                    class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                                >
                                    {{ feature.description }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Tech Stack -->
        <section id="stack" class="overflow-x-hidden py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                    <div data-aos="fade-up">
                        <p
                            class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400"
                        >
                            Technology Stack
                        </p>
                        <h2
                            class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Built on proven, battle-tested tools
                        </h2>
                        <p
                            class="mt-4 text-slate-600 leading-relaxed dark:text-slate-400"
                        >
                            The foundation combines Laravel's expressive backend
                            with a modern Vue 3 frontend, bridged by Inertia.js
                            for a fluid developer and user experience.
                        </p>

                        <dl class="mt-10 space-y-6">
                            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                                <dt
                                    class="shrink-0 text-sm font-semibold text-slate-900 sm:w-28 dark:text-white"
                                >
                                    Backend
                                </dt>
                                <dd
                                    class="text-sm text-slate-600 dark:text-slate-400"
                                >
                                    Laravel {{ laravelVersion }}, PHP
                                    {{ phpVersion }}, Sanctum, Eloquent ORM
                                </dd>
                            </div>
                            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                                <dt
                                    class="shrink-0 text-sm font-semibold text-slate-900 sm:w-28 dark:text-white"
                                >
                                    Frontend
                                </dt>
                                <dd
                                    class="text-sm text-slate-600 dark:text-slate-400"
                                >
                                    Vue 3, Inertia.js, Tailwind CSS, Vite
                                </dd>
                            </div>
                            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                                <dt
                                    class="shrink-0 text-sm font-semibold text-slate-900 sm:w-28 dark:text-white"
                                >
                                    Packages
                                </dt>
                                <dd
                                    class="text-sm text-slate-600 dark:text-slate-400"
                                >
                                    Spatie Media Library, Permissions, Activity
                                    Log, Intervention Image
                                </dd>
                            </div>
                            <div class="flex flex-col gap-1 sm:flex-row sm:gap-4">
                                <dt
                                    class="shrink-0 text-sm font-semibold text-slate-900 sm:w-28 dark:text-white"
                                >
                                    Tooling
                                </dt>
                                <dd
                                    class="text-sm text-slate-600 dark:text-slate-400"
                                >
                                    Ziggy routes, Laravel Breeze auth, scheduled
                                    tasks, AOS animations
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        data-aos="fade-up"
                        class="relative rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-8 lg:p-10 text-white shadow-2xl overflow-hidden"
                    >
                        <div
                            class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"
                        ></div>
                        <div
                            class="absolute right-20 bottom-0 w-24 h-24 bg-indigo-400/20 rounded-full blur-xl"
                        ></div>

                        <p
                            class="relative text-xs font-bold uppercase tracking-[0.2em] text-indigo-300"
                        >
                            Runtime Versions
                        </p>
                        <div class="relative mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="rounded-xl bg-white/10 backdrop-blur p-5 ring-1 ring-white/10"
                            >
                                <p class="text-3xl font-black">
                                    v{{ laravelVersion }}
                                </p>
                                <p class="mt-1 text-sm text-white/70">
                                    Laravel Framework
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-white/10 backdrop-blur p-5 ring-1 ring-white/10"
                            >
                                <p class="text-3xl font-black">
                                    v{{ phpVersion }}
                                </p>
                                <p class="mt-1 text-sm text-white/70">
                                    PHP Runtime
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-white/10 backdrop-blur p-5 ring-1 ring-white/10"
                            >
                                <p class="text-3xl font-black">{{ $t('Vue 3') }}</p>
                                <p class="mt-1 text-sm text-white/70">
                                    Composition API
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-white/10 backdrop-blur p-5 ring-1 ring-white/10"
                            >
                                <p class="text-3xl font-black">{{ $t('Inertia') }}</p>
                                <p class="mt-1 text-sm text-white/70">
                                    SPA Bridge
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security -->
        <section
            id="security"
            class="py-20 lg:py-28 bg-white dark:bg-slate-900/40"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    data-aos="fade-up"
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 px-6 py-12 sm:px-8 sm:py-16 lg:px-16 lg:py-20 text-white shadow-2xl"
                >
                    <div
                        class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"
                    ></div>
                    <div
                        class="absolute -left-10 bottom-0 w-40 h-40 bg-indigo-500/15 rounded-full blur-2xl"
                    ></div>

                    <div
                        class="relative grid lg:grid-cols-2 gap-10 items-center"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300"
                            >
                                Security & Reliability
                            </p>
                            <h2 class="mt-3 text-3xl font-bold tracking-tight">
                                Protected by design, backed up by default
                            </h2>
                            <p class="mt-4 text-white/75 leading-relaxed">
                                Authentication flows are guarded with Google
                                reCAPTCHA verification. Activity is logged for
                                full auditability. Database backups run
                                automatically every day — so your data stays
                                safe even when things go wrong.
                            </p>
                        </div>

                        <ul class="space-y-4">
                            <li
                                class="flex items-start gap-4 rounded-xl bg-white/10 backdrop-blur p-5 ring-1 ring-white/10"
                            >
                                <span
                                    class="flex-shrink-0 mt-0.5 w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-indigo-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"
                                        />
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-semibold">
                                        Google reCAPTCHA
                                    </p>
                                    <p class="mt-1 text-sm text-white/70">
                                        Server-side bot verification on
                                        sensitive forms and login.
                                    </p>
                                </div>
                            </li>
                            <li
                                class="flex items-start gap-4 rounded-xl bg-white/10 backdrop-blur p-5 ring-1 ring-white/10"
                            >
                                <span
                                    class="flex-shrink-0 mt-0.5 w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-indigo-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-semibold">
                                        Activity Audit Trail
                                    </p>
                                    <p class="mt-1 text-sm text-white/70">
                                        Complete log of user actions for
                                        compliance and debugging.
                                    </p>
                                </div>
                            </li>
                            <li
                                class="flex items-start gap-4 rounded-xl bg-white/10 backdrop-blur p-5 ring-1 ring-white/10"
                            >
                                <span
                                    class="flex-shrink-0 mt-0.5 w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-4 h-4 text-indigo-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"
                                        />
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-semibold">
                                        Daily Database Backups
                                    </p>
                                    <p class="mt-1 text-sm text-white/70">
                                        Automated scheduled backups via Laravel
                                        task scheduler.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </PublicSiteLayout>
</template>
