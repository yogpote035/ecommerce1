/**
 * Theme Switcher - Light/Dark Mode Toggle
 * Uses localStorage to persist user preference
 * Respects system preference if no saved preference
 */

class ThemeSwitcher {
    constructor(toggleSelector = '#theme-toggle') {
        this.toggleElement = document.querySelector(toggleSelector);
        this.htmlElement = document.documentElement;
        this.storageKey = 'ecommerce_theme';
        this.init();
    }

    /**
     * Initialize theme switcher
     */
    init() {
        const savedTheme = this.getSavedTheme();
        const fallbackTheme = this.getSystemTheme();
        const theme = savedTheme || fallbackTheme;

        this.setTheme(theme, Boolean(savedTheme));

        if (this.toggleElement) {
            this.toggleElement.addEventListener('click', () => this.toggleTheme());
        }

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!this.getSavedTheme()) {
                this.setTheme(e.matches ? 'dark' : 'light', false);
            }
        });

        window.addEventListener('storage', (e) => {
            if (e.key === this.storageKey) {
                const newTheme = e.newValue || this.getSystemTheme();
                this.setTheme(newTheme, Boolean(e.newValue));
            }
        });
    }

    /**
     * Get saved theme from localStorage
     * @returns {string|null} - 'light' or 'dark', or null if not saved
     */
    getSavedTheme() {
        return localStorage.getItem(this.storageKey);
    }

    /**
     * Get system theme preference
     * @returns {string} - 'light' or 'dark' based on system preference
     */
    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    /**
     * Set theme and save preference
     * @param {string} theme - 'light' or 'dark'
     */
    setTheme(theme, save = true) {
        if (!['light', 'dark'].includes(theme)) {
            console.warn('Invalid theme:', theme);
            return;
        }

        this.htmlElement.setAttribute('data-theme', theme);

        if (save) {
            localStorage.setItem(this.storageKey, theme);
        } else {
            localStorage.removeItem(this.storageKey);
        }

        this.updateToggleButton(theme);

        window.dispatchEvent(new CustomEvent('themechange', {
            detail: { theme }
        }));
    }

    /**
     * Toggle between light and dark themes
     */
    toggleTheme() {
        const currentTheme = this.htmlElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
    }

    /**
     * Update toggle button appearance
     * @param {string} theme - Current theme
     */
    updateToggleButton(theme) {
        if (!this.toggleElement) return;

        const lightIcon = `
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="theme-icon" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M12 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
          </svg>`;

        const darkIcon = `
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="theme-icon" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/>
          </svg>`;

        this.toggleElement.innerHTML = theme === 'dark' ? lightIcon : darkIcon;
        this.toggleElement.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        this.toggleElement.classList.toggle('dark-mode', theme === 'dark');
        this.toggleElement.classList.toggle('light-mode', theme !== 'dark');
    }

    /**
     * Get current theme
     * @returns {string} - Current theme ('light' or 'dark')
     */
    getCurrentTheme() {
        return this.htmlElement.getAttribute('data-theme') || 'light';
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ThemeSwitcher();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSwitcher;
}
