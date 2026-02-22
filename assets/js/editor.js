/**
 * CodeDojo - Code Editor JavaScript
 * Handles live preview, auto-save, and editor controls
 */

class CodeEditor {
    constructor() {
        this.htmlCode = document.getElementById('htmlCode');
        this.previewFrame = document.getElementById('preview');
        this.previewPanel = document.getElementById('previewPanel');
        this.fullscreenBtn = document.getElementById('fullscreenBtn');
        this.runBtn = document.getElementById('runBtn');
        this.resetBtn = document.getElementById('resetBtn');
        this.saveBtn = document.getElementById('saveBtn');
        this.showHintBtn = document.getElementById('showHintBtn');
        this.hintContent = document.getElementById('hintContent');
        this.hints = Array.isArray(window.CODEDOJO_CONTEXT?.hints) ? window.CODEDOJO_CONTEXT.hints : [];
        this.hintIndex = -1;

        this.starterCode = '';
        this.isFullscreen = false;

        this.init();
    }

    init() {
        this.loadStarterCode();
        setInterval(() => this.autoSave(), 30000);

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
        if (this.fullscreenBtn) {
            this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
        }

        document.addEventListener('fullscreenchange', () => this.handleFullscreenChange());
        document.addEventListener('webkitfullscreenchange', () => this.handleFullscreenChange());

        setTimeout(() => this.runCode(), 500);

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                this.runCode();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.saveCode();
            }
        });
    }

    loadStarterCode() {
        const urlParams = new URLSearchParams(window.location.search);
        const taskId = urlParams.get('task');
        if (taskId) {
            this.starterCode = this.htmlCode.value;
            return;
        }

        const saved = localStorage.getItem('codedojo_autosave');
        if (saved) {
            this.htmlCode.value = saved;
        } else {
            this.starterCode = this.htmlCode.value;
        }
    }

    runCode() {
        const code = this.htmlCode.value;
        const preview = this.previewFrame.contentDocument || this.previewFrame.contentWindow.document;

        preview.open();
        preview.write(code);
        preview.close();

        this.showFeedback('Code executed!', 'success');
    }

    resetCode() {
        if (confirm('Are you sure you want to reset? This will clear your current code.')) {
            this.htmlCode.value = this.starterCode;
            this.runCode();
            localStorage.removeItem('codedojo_autosave');
            this.showFeedback('Code reset to starter template', 'info');
        }
    }

    async saveCode() {
        const code = this.htmlCode.value.trim();
        if (!code) {
            this.showFeedback('Please write some code first!', 'warning');
            return;
        }

        const title = prompt('Give your practice a title:', 'My Practice ' + new Date().toLocaleDateString());
        if (!title) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('html_code', code);
            formData.append('task_id', new URLSearchParams(window.location.search).get('task') || '');

            const response = await fetch('api/save_practice.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showFeedback('Practice saved successfully!', 'success');
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

    autoSave() {
        const code = this.htmlCode.value;
        if (code) {
            localStorage.setItem('codedojo_autosave', code);
        }
    }

    toggleHint() {
        if (!this.hintContent) {
            return;
        }

        // Progressive hint ladder: step 1 -> step 2 -> step 3 -> reset.
        if (this.hints.length === 0) {
            this.hintContent.classList.toggle('visible');
            if (this.hintContent.classList.contains('visible')) {
                this.showHintBtn.innerHTML = '<span class="material-icons">visibility_off</span> Hide Hint';
            } else {
                this.showHintBtn.innerHTML = '<span class="material-icons">lightbulb</span> Show Hint';
            }
            return;
        }

        this.hintIndex += 1;
        if (this.hintIndex >= this.hints.length) {
            this.hintIndex = -1;
            this.hintContent.classList.remove('visible');
            this.hintContent.textContent = 'Click "Show Hint" to reveal step 1 of 3.';
            this.showHintBtn.innerHTML = '<span class="material-icons">lightbulb</span> Show Hint';
            return;
        }

        const step = this.hintIndex + 1;
        this.hintContent.classList.add('visible');
        this.hintContent.textContent = this.hints[this.hintIndex];

        if (step < this.hints.length) {
            this.showHintBtn.innerHTML = `<span class="material-icons">navigate_next</span> Next Hint (${step + 1}/${this.hints.length})`;
        } else {
            this.showHintBtn.innerHTML = '<span class="material-icons">visibility_off</span> Hide Hints';
        }
    }

    toggleFullscreen() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            if (this.previewPanel.requestFullscreen) {
                this.previewPanel.requestFullscreen();
            } else if (this.previewPanel.webkitRequestFullscreen) {
                this.previewPanel.webkitRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
        }
    }

    handleFullscreenChange() {
        const isCurrentlyFullscreen = document.fullscreenElement === this.previewPanel ||
            document.webkitFullscreenElement === this.previewPanel;

        this.isFullscreen = isCurrentlyFullscreen;

        if (this.isFullscreen) {
            this.fullscreenBtn.innerHTML = '<span class="material-icons">fullscreen_exit</span>';
            this.previewPanel.classList.add('fullscreen-active');
        } else {
            this.fullscreenBtn.innerHTML = '<span class="material-icons">fullscreen</span>';
            this.previewPanel.classList.remove('fullscreen-active');
        }
    }

    showFeedback(message, type = 'info') {
        const existing = document.querySelector('.feedback-toast');
        if (existing) {
            existing.remove();
        }

        const toast = document.createElement('div');
        toast.className = `feedback-toast feedback-${type}`;
        toast.textContent = message;

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

        const colors = {
            success: 'background: #10b981; color: white;',
            danger: 'background: #ef4444; color: white;',
            warning: 'background: #f59e0b; color: white;',
            info: 'background: #3b82f6; color: white;'
        };

        toast.style.cssText += colors[type] || colors.info;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('htmlCode')) {
        new CodeEditor();
    }
});

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);
