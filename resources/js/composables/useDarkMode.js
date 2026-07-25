import { ref } from 'vue';

// Reactive variable to store the current theme state (true = dark mode, false = light mode)
// It checks if the <html> element already has the 'dark' class.
const isDark = ref(
    typeof document !== 'undefined' &&
        document.documentElement.classList.contains('dark')
);

/**
 * Apply the selected theme across the application.
 *
 * @param {boolean} dark - true to enable dark mode, false for light mode.
 */
const applyTheme = (dark) => {
    // Update the reactive state
    isDark.value = dark;

    // Add or remove the 'dark' class from the <html> element
    // Tailwind CSS uses this class to activate dark mode styles.
    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', dark);
    }

    // Save the user's theme preference in localStorage
    // so it remains after page refresh or browser restart.
    try {
        localStorage.setItem('theme', dark ? 'dark' : 'light');
    } catch (e) {
        // Ignore errors (e.g., localStorage unavailable in private browsing)
    }
};

/**
 * Custom Vue composable for managing dark mode.
 *
 * Returns:
 * - isDark: Reactive boolean indicating current theme.
 * - toggleDarkMode: Function to switch between dark and light mode.
 */
export function useDarkMode() {
    /**
     * Toggle the current theme.
     * If dark mode is enabled, switch to light mode.
     * If light mode is enabled, switch to dark mode.
     */
    const toggleDarkMode = () => applyTheme(!isDark.value);

    return { isDark, toggleDarkMode };
}