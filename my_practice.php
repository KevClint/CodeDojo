<?php
/**
 * CodeDojo - Projects Page
 * View and manage saved editor projects
 */

$pageTitle = 'Projects';
$currentPage = 'practice';
$includePractice = true;

include 'includes/header.php';
?>

<div style="padding: var(--spacing-xl); max-width: 1400px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl); flex-wrap: wrap; gap: var(--spacing-md);">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: var(--text-primary); margin-bottom: var(--spacing-xs);">
                Projects
            </h1>
            <p style="font-size: 16px; color: var(--text-secondary); margin: 0;">
                Saved editor projects you can reopen and continue
            </p>
        </div>
        <a href="editor.php" class="btn btn-primary">
            <span class="material-icons">add</span>
            New Project
        </a>
    </div>

    <div id="practiceGrid" class="practice-grid">
        <div style="grid-column: 1 / -1; text-align: center; padding: var(--spacing-xl);">
            <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid var(--color-primary); border-top-color: transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="color: var(--text-secondary); margin-top: var(--spacing-md);">Loading your projects...</p>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<?php include 'includes/footer.php'; ?>
