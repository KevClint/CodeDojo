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

class SidebarMenuManager {
    constructor() {
        this.sidebar = document.getElementById('sidebarNav');
        this.menuToggle = document.getElementById('menuToggle');
        this.overlay = document.getElementById('sidebarOverlay');
        this.mobileQuery = window.matchMedia('(max-width: 768px)');

        this.init();
    }

    init() {
        if (!this.sidebar || !this.menuToggle || !this.overlay) {
            return;
        }

        this.menuToggle.addEventListener('click', () => this.toggle());
        this.overlay.addEventListener('click', () => this.close());

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.close();
            }
        });

        this.sidebar.querySelectorAll('.nav-item').forEach((item) => {
            item.addEventListener('click', () => {
                if (this.isMobile()) {
                    this.close();
                }
            });
        });

        window.addEventListener('resize', () => {
            if (!this.isMobile()) {
                this.close();
            }
        });
    }

    isMobile() {
        return this.mobileQuery.matches;
    }

    open() {
        if (this.isMobile()) {
            this.sidebar.classList.add('active');
        } else {
            this.sidebar.classList.add('expanded');
        }
        this.overlay.classList.add('active');
        document.body.classList.add('sidebar-open');
        this.menuToggle.setAttribute('aria-expanded', 'true');
        this.menuToggle.setAttribute('aria-label', 'Close menu');

        const icon = this.menuToggle.querySelector('.material-icons');
        if (icon) {
            icon.textContent = 'close';
        }
    }

    close() {
        this.sidebar.classList.remove('active');
        this.sidebar.classList.remove('expanded');
        this.overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        this.menuToggle.setAttribute('aria-expanded', 'false');
        this.menuToggle.setAttribute('aria-label', 'Open menu');

        const icon = this.menuToggle.querySelector('.material-icons');
        if (icon) {
            icon.textContent = 'menu';
        }
    }

    toggle() {
        if (this.sidebar.classList.contains('active') || this.sidebar.classList.contains('expanded')) {
            this.close();
            return;
        }
        this.open();
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ThemeManager();
    new SidebarMenuManager();
});
