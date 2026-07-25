<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { onMounted, nextTick } from "vue";
import AOS from "aos";
import "aos/dist/aos.css";

onMounted(() => {
    nextTick(() => {
        AOS.init({
            duration: 800,
            once: true,
            mirror: false,
        });
    });
});

const stats = [
    {
        title: "Open job requests",
        value: "0",
        iconPath: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6",
    },
    {
        title: "In review (Inspections)",
        value: "0",
        iconPath: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    },
    {
        title: "Trainings today",
        value: "0",
        iconPath: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
    },
];

const quickActions = [
    {
        title: "Add New User",
        description: "Create a user account and assign a role.",
        route: "users.create",
        iconPath:
            "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
        accent: "indigo",
    },
    {
        title: "Manage Users",
        description: "View, edit, and manage all system users.",
        route: "users.index",
        iconPath:
            "M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z",
        accent: "indigo",
    },
    {
        title: "Add New Role",
        description: "Define a new role with permissions.",
        route: "roles.create",
        iconPath: "M12 4.5v15m7.5-7.5h-15",
        accent: "indigo",
    },
    {
        title: "Manage Roles",
        description: "Update roles and access permissions.",
        route: "roles.index",
        iconPath:
            "M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z",
        accent: "indigo",
    },
    {
        title: "Activity Logs",
        description: "Review system changes and audit history.",
        route: "activity.index",
        iconPath: "M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z",
        accent: "indigo",
    },
    {
        title: "My Profile",
        description: "Update your account and security settings.",
        route: "profile.edit",
        iconPath:
            "M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z",
        accent: "indigo",
    },
];

const accentStyles = {
    indigo: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-400",
    },
    sky: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
    },
    violet: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
    },
    emerald: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
    },
    amber: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
    },
    rose: {
        icon: "bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white dark:bg-indigo-500/15 dark:text-indigo-300",
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="max-w-[1700px] mx-auto space-y-4 lg:space-y-8 p-4 lg:p-8">
            <!-- Welcome Banner - Added Gradient for a more premium look -->
            <div
                data-aos="fade-down"
                class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 rounded-xl p-6 lg:p-10 text-white relative overflow-hidden shadow-lg"
            >
                <div class="relative z-10">
                    <h2
                        class="text-xs lg:text-3xl font-bold opacity-90 uppercase tracking-widest"
                    >
                        OVERVIEW whole System
                    </h2>
                    <p
                        class="mt-3 text-white/80 text-sm lg:text-base max-w-lg leading-relaxed"
                    >
                        Quick snapshot of open job requests, inspections in
                        review and trainings scheduled for today.
                    </p>
                </div>
                <!-- Decorative Circles -->
                <div
                    class="absolute -right-10 -top-10 w-40 h-40 lg:w-64 lg:h-64 bg-white/10 rounded-full blur-2xl"
                ></div>
                <div
                    class="absolute right-20 bottom-0 w-20 h-20 bg-white/5 rounded-full blur-xl"
                ></div>
            </div>

            <!-- Stats Grid -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6"
            >
                <div
                    v-for="(stat, index) in stats"
                    :key="stat.title"
                    data-aos="fade-up"
                    :data-aos-delay="index * 100"
                    data-aos-duration="1000"
                    data-aos-easing="ease-out-back"
                    class="group bg-white p-5 lg:p-6 rounded-sm shadow-sm border border-slate-100 flex justify-between items-start transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-xl hover:border-indigo-200 dark:bg-slate-800 dark:border-slate-700 dark:hover:border-indigo-500/40"
                >
                    <div>
                        <!-- Title with slightly more letter spacing -->
                        <p
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] dark:text-slate-500"
                        >
                            {{ stat.title }}
                        </p>

                        <!-- Value with a subtle scale effect on card hover -->
                        <p
                            class="text-3xl lg:text-4xl font-black text-gray-800 mt-2 transition-transform duration-500 group-hover:scale-105 origin-left dark:text-slate-100"
                        >
                            {{ stat.value }}
                        </p>
                    </div>

                    <!-- Icon Container: Changes color on card hover -->
                    <div
                        class="bg-slate-50 p-3 rounded-sm text-slate-400 transition-all duration-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 group-hover:rotate-3 dark:bg-slate-700 dark:text-slate-300"
                    >
                        <svg
                            class="w-6 h-6 lg:w-8 lg:h-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                :d="stat.iconPath"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <section
                data-aos="fade-up"
                class="theme-form-card overflow-hidden"
            >
                <div
                    class="theme-form-section-header flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h3 class="theme-form-section-title">Quick Actions</h3>
                        <p class="mt-1 text-xs text-slate-400">
                            Jump to the most used admin workflows.
                        </p>
                    </div>
                    <span
                        class="inline-flex w-fit items-center rounded-full bg-slate-700/60 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-300"
                    >
                        {{ quickActions.length }} shortcuts
                    </span>
                </div>

                <div class="p-2 sm:p-3">
                    <div
                        class="grid grid-cols-1 overflow-hidden rounded-xl bg-slate-200 sm:grid-cols-2 xl:grid-cols-3 dark:bg-slate-700 gap-px"
                    >
                        <Link
                            v-for="action in quickActions"
                            :key="action.title"
                            :href="route(action.route)"
                            class="group flex items-center gap-4 bg-white p-4 lg:p-5 transition-colors hover:bg-indigo-50/80 dark:bg-slate-800 dark:hover:bg-indigo-500/10"
                        >
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-all duration-300 group-hover:scale-105"
                                :class="accentStyles[action.accent].icon"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        :d="action.iconPath"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <h4
                                        class="truncate text-sm font-bold text-slate-800 dark:text-slate-100"
                                    >
                                        {{ action.title }}
                                    </h4>
                                    <svg
                                        class="h-4 w-4 shrink-0 text-slate-300 transition-all duration-300 group-hover:translate-x-0.5 group-hover:text-indigo-500 dark:text-slate-600 dark:group-hover:text-indigo-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8.25 4.5l7.5 7.5-7.5 7.5"
                                        />
                                    </svg>
                                </div>
                                <p
                                    class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                >
                                    {{ action.description }}
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
