import { ref, watchEffect } from 'vue';

const STORAGE_KEY = 'kt-theme';
const THEME_MODES = {
    LIGHT: 'light',
    DARK: 'dark',
    SYSTEM: 'system',
};

// Global reactive theme state
const theme = ref(THEME_MODES.LIGHT);

/**
 * Composable for managing application theme (light/dark mode)
 * Compatible with Metronic template theme system
 */
export function useTheme() {
    /**
     * Initialize theme from localStorage or system preference
     */
    const initTheme = () => {
        const stored = localStorage.getItem(STORAGE_KEY);

        if (stored && Object.values(THEME_MODES).includes(stored)) {
            theme.value = stored;
        } else {
            // Default to light theme (ignore system preference for now)
            theme.value = THEME_MODES.LIGHT;
        }

        applyTheme();
    };

    /**
     * Apply theme to document element
     */
    const applyTheme = () => {
        const root = document.documentElement;

        // Remove existing theme classes
        root.classList.remove('light', 'dark');

        // Determine actual theme (resolve 'system' to 'light' or 'dark')
        let appliedTheme = theme.value;
        if (appliedTheme === THEME_MODES.SYSTEM) {
            appliedTheme = window.matchMedia('(prefers-color-scheme: dark)').matches
                ? THEME_MODES.DARK
                : THEME_MODES.LIGHT;
        }

        // Apply theme class
        root.classList.add(appliedTheme);

        // Set data attributes for Metronic compatibility
        root.setAttribute('data-kt-theme', 'true');
        root.setAttribute('data-kt-theme-mode', appliedTheme);
    };

    /**
     * Set theme mode
     * @param {string} mode - 'light', 'dark', or 'system'
     */
    const setTheme = (mode) => {
        if (!Object.values(THEME_MODES).includes(mode)) {
            console.warn(`Invalid theme mode: ${mode}`);
            return;
        }

        theme.value = mode;
        localStorage.setItem(STORAGE_KEY, mode);
        applyTheme();
    };

    /**
     * Toggle between light and dark themes
     */
    const toggleTheme = () => {
        const currentTheme = theme.value === THEME_MODES.SYSTEM
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? THEME_MODES.DARK : THEME_MODES.LIGHT)
            : theme.value;

        setTheme(currentTheme === THEME_MODES.DARK ? THEME_MODES.LIGHT : THEME_MODES.DARK);
    };

    /**
     * Get current effective theme (resolves 'system' to actual theme)
     */
    const getCurrentTheme = () => {
        if (theme.value === THEME_MODES.SYSTEM) {
            return window.matchMedia('(prefers-color-scheme: dark)').matches
                ? THEME_MODES.DARK
                : THEME_MODES.LIGHT;
        }
        return theme.value;
    };

    // Watch for system theme changes when in system mode
    watchEffect(() => {
        if (theme.value === THEME_MODES.SYSTEM) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const handler = () => applyTheme();
            mediaQuery.addEventListener('change', handler);

            return () => mediaQuery.removeEventListener('change', handler);
        }
    });

    return {
        theme,
        setTheme,
        toggleTheme,
        initTheme,
        getCurrentTheme,
        THEME_MODES,
    };
}
