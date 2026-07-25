<script setup>
import { ref, reactive, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useDarkMode } from "@/composables/useDarkMode";

const { isDark, toggleDarkMode } = useDarkMode();

const isSidebarOpen = ref(false);
const isProfileMenuOpen = ref(false);

// Close sidebar and profile menu when navigating
watch(
    () => usePage().url,
    () => {
        isSidebarOpen.value = false;
        isProfileMenuOpen.value = false;
    },
);

const userInitials = () => {
    const name = usePage().props.auth.user.name;
    return name
        .split(" ")
        .map((part) => part[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();
};

const isProfileActive = () => route().current("profile.edit");

// Dynamic Navigation with "Active" state detection
const navItems = [
    {
        name: "Dashboard",
        icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
        route: "dashboard",
        active: route().current("dashboard"),
    },
    { category: "MAIN MODULES" },
    {
        name: "User Management",
        icon: "M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z",
        route: "users.index",
        active: route().current("users.*"),
    },
    {
        name: "Product Management",
        icon: "M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z",
        active:
            route().current("brands.*") ||
            route().current("product-categories.*") ||
            route().current("units.*") ||
            route().current("products.*") ||
            route().current("product-selling-prices.*") ||
            route().current("warehouses.*"),
        children: [
            {
                name: "Brands",
                route: "brands.index",
                active: route().current("brands.*"),
            },
            {
                name: "Category",
                route: "product-categories.index",
                active: route().current("product-categories.*"),
            },
            {
                name: "Unit",
                route: "units.index",
                active: route().current("units.*"),
            },
            {
                name: "Product Main",
                route: "products.index",
                active: route().current("products.*"),
            },
            {
                name: "Selling Prices",
                route: "product-selling-prices.index",
                active: route().current("product-selling-prices.*"),
            },
            {
                name: "WareHose",
                route: "warehouses.index",
                active: route().current("warehouses.*"),
            },
        ],
    },
    {
        name: "Roles & Permissions",
        icon: "M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z",
        route: "roles.index",
        active: route().current("roles.*"),
    },
    {
        name: "Activity Logs",
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
        route: "activity.index",
        active: route().current("activity.*"),
    },
];

// Track open/closed state for dropdown nav items, keyed by item.name.
// Auto-open any dropdown whose parent (or a child) is currently active.
const openDropdowns = reactive(
    navItems.reduce((acc, item) => {
        if (item.children) {
            acc[item.name] = item.active;
        }
        return acc;
    }, {}),
);

const toggleDropdown = (name) => {
    openDropdowns[name] = !openDropdowns[name];
};
</script>

<template>
    <div
        class="theme-app-bg h-screen flex overflow-hidden font-sans transition-colors"
    >
        <!-- MOBILE OVERLAY -->
        <transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isSidebarOpen"
                @click="isSidebarOpen = false"
                class="fixed inset-0 bg-black/60 z-40 lg:hidden"
            ></div>
        </transition>

        <!-- SIDEBAR -->
        <aside
            class="theme-sidebar fixed inset-y-0 left-0 z-50 w-72 lg:w-64 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
            :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Sidebar Header -->
            <div
                class="p-6 border-b border-slate-200 shrink-0 flex items-center justify-between dark:border-slate-700"
            >
                <div class="flex items-center space-x-3">
                    <div
                        class="bg-indigo-600 p-2 rounded-lg shadow-lg shadow-indigo-600/30"
                    >
                        <svg
                            class="w-6 h-6 text-white"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <path
                                d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"
                            ></path>
                        </svg>
                    </div>
                    <span
                        class="font-bold text-sm lg:text-base tracking-wide text-slate-800 dark:text-white"
                        >Sample Project</span
                    >
                </div>
                <button
                    @click="isSidebarOpen = false"
                    class="lg:hidden p-2 text-slate-700 dark:text-white"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable Nav Links -->
            <!-- Scrollable Nav Links -->
            <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar">
                <template v-for="item in navItems" :key="item.name">
                    <!-- Category Header -->
                    <div
                        v-if="item.category"
                        class="theme-sidebar-category px-7 pt-3 pb-3 text-[10px] font-semibold tracking-[0.15em] uppercase"
                    >
                        {{ item.category }}
                    </div>

                    <!-- Dropdown Item (has children) -->
                    <div v-else-if="item.children" class="px-3 mb-2">
                        <button
                            type="button"
                            @click="toggleDropdown(item.name)"
                            class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 ease-in-out"
                            :class="
                                item.active
                                    ? 'theme-sidebar-nav-active'
                                    : 'theme-sidebar-nav-inactive'
                            "
                        >
                            <span class="flex items-center">
                                <svg
                                    class="w-5 h-5 mr-3 transition-colors duration-200"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                >
                                    <path
                                        :d="item.icon"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <span
                                    class="text-sm font-semibold tracking-wide"
                                >
                                    {{ item.name }}
                                </span>
                            </span>

                            <!-- Chevron -->
                            <svg
                                class="w-4 h-4 transition-transform duration-200"
                                :class="{
                                    'rotate-180': openDropdowns[item.name],
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                            >
                                <path
                                    d="M19 9l-7 7-7-7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>

                        <!-- Children (collapsible) -->
                        <div
                            v-show="openDropdowns[item.name]"
                            class="mt-1 ml-6 pl-4 border-l theme-sidebar-nav-inactive space-y-1"
                        >
                            <Link
                                v-for="child in item.children"
                                :key="child.name"
                                :href="route(child.route)"
                                class="block px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 ease-in-out"
                                :class="
                                    child.active
                                        ? 'theme-sidebar-nav-active'
                                        : 'theme-sidebar-nav-inactive'
                                "
                            >
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Plain Navigation Link -->
                    <div v-else class="px-3 mb-2">
                        <Link
                            :href="route(item.route)"
                            class="group flex items-center px-4 py-3 rounded-xl transition-all duration-200 ease-in-out"
                            :class="
                                item.active
                                    ? 'theme-sidebar-nav-active'
                                    : 'theme-sidebar-nav-inactive'
                            "
                        >
                            <svg
                                class="w-5 h-5 mr-3 transition-colors duration-200"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                            >
                                <path
                                    :d="item.icon"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <span class="text-sm font-semibold tracking-wide">
                                {{ item.name }}
                            </span>
                        </Link>
                    </div>
                </template>
            </nav>

            <!-- Fixed Bottom Profile -->
            <div class="theme-sidebar-footer relative p-4 shrink-0">
                <!-- Dropdown Menu -->
                <transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-2 scale-95"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="opacity-100 translate-y-0 scale-100"
                    leave-to-class="opacity-0 translate-y-2 scale-95"
                >
                    <div
                        v-if="isProfileMenuOpen"
                        class="absolute bottom-full left-3 right-3 mb-3 z-[60] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-300/40 ring-1 ring-slate-200 dark:border-slate-600/90 dark:bg-slate-700 dark:shadow-slate-900/30 dark:ring-slate-500/20"
                    >
                        <div
                            class="border-b border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-600 dark:bg-slate-700/90"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400"
                            >
                                Signed in as
                            </p>
                            <p
                                class="mt-1 truncate text-sm font-bold text-slate-800 dark:text-white"
                            >
                                {{ $page.props.auth.user.name }}
                            </p>
                            <p
                                class="mt-0.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                {{ $page.props.auth.user.email }}
                            </p>
                        </div>

                        <div class="p-2">
                            <Link
                                :href="route('profile.edit')"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-colors"
                                :class="
                                    isProfileActive()
                                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25'
                                        : 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-200 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-200'
                                "
                            >
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg"
                                    :class="
                                        isProfileActive()
                                            ? 'bg-indigo-500/40 text-white'
                                            : 'bg-slate-100 text-indigo-600 dark:bg-slate-600 dark:text-indigo-300'
                                    "
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                        />
                                    </svg>
                                </span>
                                My Profile
                            </Link>

                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-200 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-200"
                            >
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-indigo-600 dark:bg-slate-600/70 dark:text-indigo-300"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"
                                        />
                                    </svg>
                                </span>
                                Log Out
                            </Link>
                        </div>
                    </div>
                </transition>

                <button
                    @click="isProfileMenuOpen = !isProfileMenuOpen"
                    class="group flex w-full items-center space-x-3 rounded-xl p-3 transition-colors hover:bg-indigo-500/10"
                    :class="{
                        'bg-indigo-500/10 ring-1 ring-indigo-500/40':
                            isProfileMenuOpen,
                    }"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-indigo-400/40 bg-indigo-600 text-xs font-bold text-white shadow-md shadow-indigo-600/25"
                    >
                        {{ userInitials() }}
                    </div>
                    <div
                        class="flex-1 overflow-hidden text-left text-slate-800 dark:text-white"
                    >
                        <p class="truncate text-xs font-semibold leading-none">
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p
                            class="mt-1 text-[10px] text-slate-500 dark:text-slate-400"
                        >
                            Administrator
                        </p>
                    </div>
                    <svg
                        class="h-4 w-4 text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': isProfileMenuOpen }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                    >
                        <path d="M5 15l7-7 7 7" />
                    </svg>
                </button>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- HEADER -->
            <header
                class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 lg:px-8 shrink-0 z-30 shadow-sm transition-colors dark:bg-slate-800 dark:border-slate-700"
            >
                <div class="flex items-center">
                    <button
                        @click="isSidebarOpen = true"
                        class="p-2 -ml-2 mr-3 text-gray-500 lg:hidden dark:text-slate-300"
                    >
                        <svg
                            class="w-7 h-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                        >
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1
                            class="text-sm lg:text-lg font-bold text-slate-800 leading-none dark:text-slate-100"
                        >
                            Dashboard
                        </h1>
                        <p
                            class="hidden sm:block text-[10px] text-slate-400 uppercase tracking-widest mt-1 dark:text-slate-500"
                        >
                            Laravel Inertia Vue Admin
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Light / Dark Mode Toggle -->
                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="relative inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40 dark:border-slate-600 dark:bg-slate-700 dark:text-indigo-200 dark:hover:bg-slate-600"
                        :title="
                            isDark
                                ? 'Switch to light mode'
                                : 'Switch to dark mode'
                        "
                        :aria-label="
                            isDark
                                ? 'Switch to light mode'
                                : 'Switch to dark mode'
                        "
                    >
                        <!-- Sun (shown in dark mode, click to go light) -->
                        <svg
                            v-if="isDark"
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
                            />
                        </svg>
                        <!-- Moon (shown in light mode, click to go dark) -->
                        <svg
                            v-else
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"
                            />
                        </svg>
                    </button>
                    <a
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group hidden sm:inline-flex items-center gap-2 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-700 hover:shadow dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 dark:hover:border-indigo-400/40 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
                    >
                        <span
                            class="flex h-5 w-5 items-center justify-center rounded-md bg-white text-slate-500 transition-colors group-hover:text-indigo-600 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:text-indigo-300"
                        >
                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13.5 3H21m0 0v7.5M21 3l-9.75 9.75M7.5 6H6A2.25 2.25 0 003.75 8.25v9.75A2.25 2.25 0 006 20.25h9.75A2.25 2.25 0 0018 18v-1.5"
                                />
                            </svg>
                        </span>
                        View Site
                    </a>
                    <span
                        class="hidden md:block text-sm font-semibold text-slate-700 dark:text-slate-200"
                        >{{ $page.props.auth.user.name }} (
                        {{ $page.props.auth.user.user_type }})</span
                    >
                    <div
                        class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold uppercase shadow-sm"
                    >
                        {{ userInitials() }}
                    </div>
                </div>
            </header>

            <!-- SCROLLABLE MAIN & FOOTER -->
            <main
                class="theme-content-bg flex-1 overflow-y-auto p-4 lg:p-8 custom-scrollbar flex flex-col transition-colors"
            >
                <div class="flex-1 max-w-[1600px] mx-auto w-full">
                    <!-- This SLOT is where your specific page content will appear -->
                    <slot />
                </div>

                <!-- FOOTER -->
                <footer
                    class="mt-1 pt-8 pb-3 border-t border-slate-200 text-center text-slate-500 text-[10px] lg:text-xs dark:border-slate-700 dark:text-slate-500"
                >
                    <div class="uppercase tracking-widest font-bold">
                        Sample Project for Laravel Inertia Vue3 Admin Dashboard
                        Layout
                    </div>
                </footer>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 10px;
}
aside .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
}
</style>
