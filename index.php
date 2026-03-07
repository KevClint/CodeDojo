<?php
/**
 * CodeDojo - Home Page
 * Editor-first landing page
 */

$pageTitle = 'CodeDojo Playground';
$currentPage = 'home';

include 'includes/header.php';

$practiceCount = 0;
$taskCount = 0;
$recentProjects = [];

try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    $practiceCount = (int) $pdo->query("SELECT COUNT(*) FROM user_practice")->fetchColumn();
    $taskCount = (int) $pdo->query("SELECT COUNT(*) FROM practice_tasks")->fetchColumn();

    $recentStmt = $pdo->query("SELECT id, title, updated_at, created_at FROM user_practice ORDER BY updated_at DESC, created_at DESC LIMIT 4");
    $recentProjects = $recentStmt->fetchAll();
} catch (Throwable $e) {
    $practiceCount = 0;
    $taskCount = 0;
    $recentProjects = [];
}
?>

<section class="editor-home">
    <div class="editor-home-hero">
        <p class="editor-home-kicker">Build in the browser</p>
        <h2>Code editor first. Save Projects like your own mini CodePen.</h2>
        <p class="editor-home-copy">Write HTML, CSS, and JavaScript with instant output. Save each project and reopen it from your Projects tab anytime.</p>
        <div class="editor-home-actions">
            <a href="editor.php" class="btn btn-primary">
                <span class="material-icons">code</span>
                Open Editor
            </a>
            <a href="my_practice.php" class="btn btn-secondary">
                <span class="material-icons">folder</span>
                Open Projects
            </a>
        </div>
    </div>

    <div class="editor-home-strip">
        <article class="editor-home-mini">
            <span class="material-icons">bolt</span>
            <div>
                <h4>Instant Preview</h4>
                <p>Run HTML, CSS, and JS in one click.</p>
            </div>
        </article>
        <article class="editor-home-mini">
            <span class="material-icons">folder_copy</span>
            <div>
                <h4>Project Library</h4>
                <p>Keep your work and reopen anytime.</p>
            </div>
        </article>
        <article class="editor-home-mini">
            <span class="material-icons">keyboard</span>
            <div>
                <h4>Shortcuts</h4>
                <p><code>Ctrl+Enter</code> run, <code>Ctrl+S</code> save.</p>
            </div>
        </article>
    </div>

    <div class="editor-home-panel">
        <h3>Quick Start</h3>
        <a href="editor.php?template=button" class="editor-home-link">
            <span class="material-icons">smart_button</span>
            Button component starter
        </a>
        <a href="editor.php?template=card" class="editor-home-link">
            <span class="material-icons">crop_portrait</span>
            Card layout starter
        </a>
        <a href="editor.php?template=form" class="editor-home-link">
            <span class="material-icons">edit_note</span>
            Form starter
        </a>
        <div class="editor-home-stats">
            <div>
                <strong><?php echo $practiceCount; ?></strong>
                <span>Saved Projects</span>
            </div>
            <div>
                <strong><?php echo $taskCount; ?></strong>
                <span>Starter Challenges</span>
            </div>
        </div>
    </div>

    <div class="editor-home-projects">
        <div class="editor-home-section-head">
            <h3>Recent Projects</h3>
            <a href="my_practice.php" class="editor-home-inline-link">View all</a>
        </div>

        <?php if (!empty($recentProjects)): ?>
            <div class="editor-home-project-list">
                <?php foreach ($recentProjects as $project): ?>
                    <?php $projectDate = !empty($project['updated_at']) ? $project['updated_at'] : ($project['created_at'] ?? null); ?>
                    <a href="editor.php?practice=<?php echo (int) $project['id']; ?>" class="editor-home-project-item">
                        <span class="material-icons">description</span>
                        <div>
                            <strong><?php echo htmlspecialchars((string) ($project['title'] ?? 'Untitled Project')); ?></strong>
                            <small>Updated <?php echo htmlspecialchars($projectDate ? date('M j, Y', strtotime((string) $projectDate)) : 'recently'); ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="editor-home-empty">
                <span class="material-icons">hourglass_empty</span>
                <p>No projects yet. Start with a template and save your first one.</p>
                <a href="editor.php?template=button" class="btn btn-primary btn-small">
                    <span class="material-icons">play_arrow</span>
                    Start with Button Template
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="editor-home-track">
        <div class="editor-home-section-head">
            <h3>Build Path</h3>
        </div>
        <div class="editor-home-track-list">
            <a href="editor.php?template=button" class="editor-home-track-item">
                <span class="material-icons">looks_one</span>
                <div>
                    <strong>Button Basics</strong>
                    <small>Start with spacing, border, and hover states.</small>
                </div>
            </a>
            <a href="editor.php?template=card" class="editor-home-track-item">
                <span class="material-icons">looks_two</span>
                <div>
                    <strong>Card Components</strong>
                    <small>Build layout blocks with clean typography.</small>
                </div>
            </a>
            <a href="editor.php?template=form" class="editor-home-track-item">
                <span class="material-icons">looks_3</span>
                <div>
                    <strong>Form UX</strong>
                    <small>Create readable forms with proper inputs.</small>
                </div>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
