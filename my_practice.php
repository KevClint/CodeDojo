<?php
/**
 * CodeDojo - My Practice Page
 * View and manage saved practice work
 */

$pageTitle = 'My Practice';
$currentPage = 'practice';
$includePractice = true;

include 'includes/header.php';
?>

<div style="padding: var(--spacing-xl); max-width: 1400px; margin: 0 auto;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl); flex-wrap: wrap; gap: var(--spacing-md);">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: var(--spacing-xs);">
                📁 My Practice
            </h1>
            <p style="font-size: 16px; color: var(--text-secondary); margin: 0;">
                All your saved practice work in one place
            </p>
        </div>
        <a href="editor.php" class="btn btn-primary">
            <span class="material-icons">add</span>
            New Practice
        </a>
    </div>
    
    <!-- Practice Grid -->
    <div id="practiceGrid" class="practice-grid">
        <!-- Practices will be loaded here by JavaScript -->
        <div style="grid-column: 1 / -1; text-align: center; padding: var(--spacing-xl);">
            <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid var(--color-primary); border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="color: var(--text-secondary); margin-top: var(--spacing-md);">Loading your practices...</p>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Practice card hover effects are in the main CSS */
</style>

<?php include 'includes/footer.php'; ?>
