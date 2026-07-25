<script setup>
import { onMounted, nextTick } from "vue";
import AOS from "aos";
import PublicSiteHeader from "@/Components/PublicSite/PublicSiteHeader.vue";
import PublicSiteFooter from "@/Components/PublicSite/PublicSiteFooter.vue";

onMounted(() => {
    nextTick(() => {
        AOS.init({
            duration: 700,
            once: true,
            offset: 60,
            disable: false,
            // Avoid horizontal transforms that cause mobile x-scroll
            mirror: false,
        });
    });
});
</script>

<template>
    <div
        class="theme-app-bg flex min-h-screen w-full max-w-full flex-col overflow-x-hidden"
    >
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-indigo-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white"
        >
            Skip to main content
        </a>

        <PublicSiteHeader />

        <main
            id="main-content"
            class="w-full max-w-full flex-1 overflow-x-hidden pt-14 sm:pt-16"
        >
            <slot />
        </main>

        <PublicSiteFooter />
    </div>
</template>
