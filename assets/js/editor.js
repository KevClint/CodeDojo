/**
 * CodeDojo - Code Editor JavaScript
 * Handles live preview, auto-save, and editor controls
 */

class CodeEditor {
    constructor() {
        this.htmlCode = document.getElementById('htmlCode');
        this.previewFrame = document.getElementById('preview');
        this.runBtn = document.getElementById('runBtn');
        this.resetBtn = document.getElementById('resetBtn');
        this.saveBtn = document.getElementById('saveBtn');
        this.showHintBtn = document.getElementById('showHintBtn');
        this.starterCode = '';
        this.currentTaskId = null;
        
        this.init();
    }
    
    init() {
        // Load starter code if exists
        this.loadStarterCode();
        
        // Auto-save to localStorage every 30 seconds
        setInterval(() => this.autoSave(), 30000);
        
        // Event listeners
        if (this.runBtn) {
            this.runBtn.addEventListener('click', () => this.runCode());
        }
        
        if (this.resetBtn) {
            this.resetBtn.addEventListener('click', () => this.resetCode());
        }
        
        if (this.saveBtn) {
            this.saveBtn.addEventListener('click', () => this.saveCode());
        }
        
        if (this.showHintBtn) {
            this.showHintBtn.addEventListener('click', () => this.toggleHint());
        }
        
        // Auto-run on load
        setTimeout(() => this.runCode(), 500);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Enter to run code
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                this.runCode();
            }
            
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.saveCode();
            }
        });
    }
    
    /**
     * Load starter code from data attribute or localStorage
     */
    loadStarterCode() {
        const urlParams = new URLSearchParams(window.location.search);
        const taskId = urlParams.get('task');
        
        if (taskId) {
            this.currentTaskId = taskId;
            // Starter code is already loaded in PHP
            this.starterCode = this.htmlCode.value;
        } else {
            // Check localStorage for autosaved code
            const saved = localStorage.getItem('codedojo_autosave');
            if (saved) {
                this.htmlCode.value = saved;
            } else {
                this.starterCode = this.htmlCode.value;
            }
        }
    }
    
    /**
     * Run code in preview iframe
     */
    runCode() {
        const code = this.htmlCode.value;
        const preview = this.previewFrame.contentDocument || this.previewFrame.contentWindow.document;
        
        // Write code to iframe
        preview.open();
        preview.write(code);
        preview.close();
        
        // Show success feedback
        this.showFeedback('Code executed!', 'success');
    }
    
    /**
     * Reset code to starter template
     */
    resetCode() {
        if (confirm('Are you sure you want to reset? This will clear your current code.')) {
            this.htmlCode.value = this.starterCode;
            this.runCode();
            localStorage.removeItem('codedojo_autosave');
            this.showFeedback('Code reset to starter template', 'info');
        }
    }
    
    /**
     * Save code to database
     */
    async saveCode() {
        const code = this.htmlCode.value.trim();
        
        if (!code) {
            this.showFeedback('Please write some code first!', 'warning');
            return;
        }
        
        // Prompt for title
        const title = prompt('Give your practice a title:', 'My Practice ' + new Date().toLocaleDateString());
        
        if (!title) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('html_code', code);
            formData.append('task_id', this.currentTaskId || '');
            
            const response = await fetch('api/save_practice.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showFeedback('Practice saved successfully! 🎉', 'success');
                
                // Redirect to my practice after 1.5 seconds
                setTimeout(() => {
                    window.location.href = 'my_practice.php';
                }, 1500);
            } else {
                this.showFeedback(result.message || 'Failed to save', 'danger');
            }
            
        } catch (error) {
            console.error('Save error:', error);
            this.showFeedback('Error saving practice', 'danger');
        }
    }
    
    /**
     * Auto-save to localStorage
     */
    autoSave() {
        const code = this.htmlCode.value;
        if (code) {
            localStorage.setItem('codedojo_autosave', code);
            console.log('Auto-saved to localStorage');
        }
    }
    
    /**
     * Toggle hint visibility
     */
    toggleHint() {
        const hintContent = document.querySelector('.hint-content');
        if (hintContent) {
            hintContent.classList.toggle('visible');
            
            const btn = this.showHintBtn;
            if (hintContent.classList.contains('visible')) {
                btn.innerHTML = '<span class="material-icons">visibility_off</span> Hide Hint';
            } else {
                btn.innerHTML = '<span class="material-icons">lightbulb</span> Show Hint';
            }
        }
    }
    
    /**
     * Show feedback message
     */
    showFeedback(message, type = 'info') {
        // Remove existing feedback
        const existing = document.querySelector('.feedback-toast');
        if (existing) {
            existing.remove();
        }
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `feedback-toast feedback-${type}`;
        toast.textContent = message;
        
        // Add styles
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        `;
        
        // Color based on type
        const colors = {
            success: 'background: #10b981; color: white;',
            danger: 'background: #ef4444; color: white;',
            warning: 'background: #f59e0b; color: white;',
            info: 'background: #3b82f6; color: white;'
        };
        
        toast.style.cssText += colors[type] || colors.info;
        
        document.body.appendChild(toast);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize editor when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('htmlCode')) {
        new CodeEditor();
    }
});

// Add animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
