import "../css/app.css";
import "./bootstrap";

import { createInertiaApp, Link, Head } from "@inertiajs/vue3"; // 1. Import Link and Head
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

import { router } from "@inertiajs/vue3";
import AOS from "aos";
import "aos/dist/aos.css";

// Initialize AOS once
AOS.init();

// Every time an Inertia navigation happens, refresh AOS
router.on("success", () => {
    nextTick(() => {
        AOS.refresh();
    });
});

const appName = import.meta.env.VITE_APP_NAME || "Sample Application";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component("Link", Link) // 2. Register Link globally
            .component("Head", Head) // 3. Register Head globally
            .mount(el);
    },
    progress: {
        color: "#4f46e5",
    },
});
