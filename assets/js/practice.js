/**
 * CodeDojo - Practice Management
 * Handles loading, editing, and deleting saved practices
 */

class PracticeManager {
    constructor() {
        this.practiceGrid = document.getElementById('practiceGrid');
        this.init();
    }
    
    init() {
        this.loadPractices();
    }
    
    /**
     * Load all saved practices
     */
    async loadPractices() {
        try {
            const response = await fetch('api/load_practice.php');
            const result = await response.json();
            
            if (result.success && result.practices) {
                this.displayPractices(result.practices);
            } else {
                this.showEmptyState();
            }
            
        } catch (error) {
            console.error('Error loading practices:', error);
            this.showError('Failed to load practices');
        }
    }
    
    /**
     * Display practices in grid
     */
    displayPractices(practices) {
        if (!this.practiceGrid) return;
        
        this.practiceGrid.innerHTML = '';
        
        practices.forEach(practice => {
            const card = this.createPracticeCard(practice);
            this.practiceGrid.appendChild(card);
        });
    }
    
    /**
     * Create practice card element
     */
    createPracticeCard(practice) {
        const card = document.createElement('div');
        card.className = 'practice-card';
        
        // Format date
        const date = new Date(practice.created_at);
        const formattedDate = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        // Truncate code for preview
        const codePreview = this.truncateCode(practice.html_code, 100);
        
        card.innerHTML = `
            <div class="practice-card-header">
                <h3 class="practice-card-title">${this.escapeHtml(practice.title)}</h3>
                <span class="practice-card-date">${formattedDate}</span>
            </div>
            
            <div class="practice-card-preview">
                ${this.escapeHtml(codePreview)}
            </div>
            
            <div class="practice-card-actions">
                <button class="btn btn-primary btn-small" onclick="practiceManager.editPractice(${practice.id})">
                    <span class="material-icons">edit</span>
                    Edit
                </button>
                <button class="btn btn-secondary btn-small" onclick="practiceManager.viewPreview(${practice.id})">
                    <span class="material-icons">visibility</span>
                    View
                </button>
                <button class="btn btn-danger btn-small" onclick="practiceManager.deletePractice(${practice.id})">
                    <span class="material-icons">delete</span>
                    Delete
                </button>
            </div>
        `;
        
        return card;
    }
    
    /**
     * Edit a practice - redirect to editor
     */
    editPractice(id) {
        window.location.href = `editor.php?practice=${id}`;
    }
    
    /**
     * View practice preview in modal
     */
    async viewPreview(id) {
        try {
            const response = await fetch(`api/load_practice.php?id=${id}`);
            const result = await response.json();
            
            if (result.success && result.practice) {
                this.showPreviewModal(result.practice);
            }
            
        } catch (error) {
            console.error('Error loading practice:', error);
        }
    }
    
    /**
     * Show preview modal
     */
    showPreviewModal(practice) {
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 800px; background: var(--bg-primary); border-radius: var(--border-radius-lg); padding: var(--spacing-lg);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md);">
                    <h2 style="margin: 0; color: var(--text-primary);">${this.escapeHtml(practice.title)}</h2>
                    <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <iframe style="width: 100%; height: 500px; border: 1px solid var(--border-color); border-radius: var(--border-radius);"></iframe>
            </div>
        `;
        
        // Add modal styles
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: var(--spacing-lg);
        `;
        
        document.body.appendChild(modal);
        
        // Write code to iframe
        const iframe = modal.querySelector('iframe');
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write(practice.html_code);
        doc.close();
        
        // Close on overlay click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }
    
    /**
     * Delete a practice
     */
    async deletePractice(id) {
        if (!confirm('Are you sure you want to delete this practice? This cannot be undone.')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('id', id);
            
            const response = await fetch('api/delete_practice.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showFeedback('Practice deleted successfully', 'success');
                this.loadPractices(); // Reload grid
            } else {
                this.showFeedback(result.message || 'Failed to delete', 'danger');
            }
            
        } catch (error) {
            console.error('Delete error:', error);
            this.showFeedback('Error deleting practice', 'danger');
        }
    }
    
    /**
     * Show empty state
     */
    showEmptyState() {
        if (!this.practiceGrid) return;
        
        this.practiceGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <span class="material-icons" style="font-size: 64px; color: var(--text-muted);">code_off</span>
                <h3>No practices yet</h3>
                <p>Start coding to create your first practice!</p>
                <a href="editor.php" class="btn btn-primary">
                    <span class="material-icons">add</span>
                    Start Coding
                </a>
            </div>
        `;
    }
    
    /**
     * Utility: Truncate code for preview
     */
    truncateCode(code, maxLength) {
        if (code.length <= maxLength) return code;
        return code.substring(0, maxLength) + '...';
    }
    
    /**
     * Utility: Escape HTML for safe display
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Show feedback message
     */
    showFeedback(message, type = 'info') {
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
    
    /**
     * Show error message
     */
    showError(message) {
        if (!this.practiceGrid) return;
        
        this.practiceGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <span class="material-icons" style="font-size: 64px; color: var(--color-danger);">error_outline</span>
                <h3>Error Loading Practices</h3>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="location.reload()">
                    <span class="material-icons">refresh</span>
                    Retry
                </button>
            </div>
        `;
    }
}

// Initialize practice manager when DOM is ready
let practiceManager;
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('practiceGrid')) {
        practiceManager = new PracticeManager();
    }
});
