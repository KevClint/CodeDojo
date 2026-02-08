/**
 * CodeDojo - Theme Switcher
 * Handles dark/light mode toggle with localStorage persistence
 */

class ThemeManager {
    constructor() {
        this.themeToggle = document.getElementById('themeToggle');
        this.body = document.body;
        this.currentTheme = this.getStoredTheme() || 'light';
        
        this.init();
    }
    
    init() {
        // Apply stored theme on load
        this.applyTheme(this.currentTheme);
        
        // Add event listener to toggle button
        if (this.themeToggle) {
            this.themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }
    
    /**
     * Toggle between light and dark themes
     */
    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(this.currentTheme);
        this.storeTheme(this.currentTheme);
    }
    
    /**
     * Apply theme to body element
     */
    applyTheme(theme) {
        if (theme === 'dark') {
            this.body.classList.add('dark-theme');
            this.updateToggleIcon('light_mode');
        } else {
            this.body.classList.remove('dark-theme');
            this.updateToggleIcon('dark_mode');
        }
    }
    
    /**
     * Update toggle button icon
     */
    updateToggleIcon(iconName) {
        if (this.themeToggle) {
            const icon = this.themeToggle.querySelector('.material-icons');
            if (icon) {
                icon.textContent = iconName;
            }
        }
    }
    
    /**
     * Store theme preference in localStorage
     */
    storeTheme(theme) {
        localStorage.setItem('codedojo_theme', theme);
    }
    
    /**
     * Get stored theme from localStorage
     */
    getStoredTheme() {
        return localStorage.getItem('codedojo_theme');
    }
    
    /**
     * Detect system preference (optional enhancement)
     */
    getSystemPreference() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ThemeManager();
});
