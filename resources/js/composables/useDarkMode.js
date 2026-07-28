import { ref } from 'vue';

const THEME_KEY = 'theme';

const readStoredTheme = () => {
    try {
        return localStorage.getItem(THEME_KEY);
    } catch (e) {
        return null;
    }
};

/**
 * Resolve theme: only 'dark' when explicitly saved.
 * Default is always Light Mode (ignores OS preference).
 */
const resolveIsDark = () => {
    if (typeof document === 'undefined') return false;
    const stored = readStoredTheme();
    if (stored === 'dark') return true;
    if (stored === 'light') return false;
    return false; // default Light Mode
};

const isDark = ref(resolveIsDark());

const applyTheme = (dark) => {
    isDark.value = dark;

    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', dark);
    }

    try {
        localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light');
    } catch (e) {
        // Ignore (e.g. private browsing)
    }
};

// Keep Vue state in sync with the pre-paint script / DOM on load
if (typeof document !== 'undefined') {
    const dark = resolveIsDark();
    document.documentElement.classList.toggle('dark', dark);
    isDark.value = dark;
}

export function useDarkMode() {
    const toggleDarkMode = () => applyTheme(!isDark.value);
    const setDarkMode = (dark) => applyTheme(Boolean(dark));

    return { isDark, toggleDarkMode, setDarkMode };
}
