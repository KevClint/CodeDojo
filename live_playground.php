<?php
/**
 * CodeDojo - Live Playground
 * Editor-first playground with instant preview and practice saves
 */

require_once 'config/database.php';

$pageTitle = 'Live Playground';
$currentPage = 'editor';
$includePlayground = true;

$initialTitle = 'Untitled Project';
$initialHtml = "<h1>Hello, CodeDojo</h1>\n<p>Edit HTML, CSS, and JavaScript. Save this to your Projects tab.</p>";
$initialCss = "body {\n  font-family: Arial, sans-serif;\n  padding: 24px;\n}\n\nh1 {\n  color: #667eea;\n}";
$initialJs = "console.log(\"Playground ready\");";
$currentTaskId = null;
$currentPracticeId = null;
$disableRestore = false;
$challengeTag = null;

function parseSavedPracticeCode(string $saved): array
{
    $result = [
        'html' => $saved,
        'css' => '',
        'js' => ''
    ];

    if (preg_match('/<!--\s*CODEDOJO_PEN_V1:([A-Za-z0-9+\/=]+)\s*-->/i', $saved, $match)) {
        $decoded = base64_decode($match[1], true);
        if ($decoded !== false) {
            $parsed = json_decode($decoded, true);
            if (
                is_array($parsed)
                && isset($parsed['html'], $parsed['css'], $parsed['js'])
                && is_string($parsed['html'])
                && is_string($parsed['css'])
                && is_string($parsed['js'])
            ) {
                return [
                    'html' => $parsed['html'],
                    'css' => $parsed['css'],
                    'js' => $parsed['js']
                ];
            }
        }
    }

    $css = '';
    $js = '';
    $html = $saved;

    if (preg_match('/<style[^>]*>(.*?)<\/style>/is', $saved, $styleMatch)) {
        $css = trim($styleMatch[1]);
    }

    if (preg_match('/<script[^>]*>(.*?)<\/script>/is', $saved, $scriptMatch)) {
        $js = trim($scriptMatch[1]);
    }

    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $saved, $bodyMatch)) {
        $html = trim($bodyMatch[1]);
    }

    return [
        'html' => $html,
        'css' => $css,
        'js' => $js
    ];
}

if (isset($_GET['task'])) {
    $disableRestore = true;
    $currentTaskId = (int) $_GET['task'];

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT pt.id, pt.title, pt.instruction, pt.starter_code, l.title AS lesson_title FROM practice_tasks pt LEFT JOIN lessons l ON pt.lesson_id = l.id WHERE pt.id = :id');
        $stmt->execute([':id' => $currentTaskId]);
        $taskInfo = $stmt->fetch();

        if ($taskInfo) {
            $initialTitle = (string) ($taskInfo['title'] ?? $initialTitle);
            $pageTitle = 'Task: ' . $initialTitle;
            $challengeTag = !empty($taskInfo['lesson_title']) ? (string) $taskInfo['lesson_title'] : 'Challenge';

            if (!empty($taskInfo['starter_code']) && is_string($taskInfo['starter_code'])) {
                $initialHtml = $taskInfo['starter_code'];
                $initialCss = '';
                $initialJs = '';
            }
        }
    } catch (Throwable $e) {
        error_log('Playground task load error: ' . $e->getMessage());
    }
} elseif (isset($_GET['practice'])) {
    $disableRestore = true;
    $currentPracticeId = (int) $_GET['practice'];

    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT id, title, html_code, task_id FROM user_practice WHERE id = :id');
        $stmt->execute([':id' => $currentPracticeId]);
        $practice = $stmt->fetch();

        if ($practice) {
            $initialTitle = !empty($practice['title']) ? (string) $practice['title'] : $initialTitle;
            $pageTitle = 'Edit: ' . $initialTitle;
            $currentTaskId = !empty($practice['task_id']) ? (int) $practice['task_id'] : null;

            $parsed = parseSavedPracticeCode((string) ($practice['html_code'] ?? ''));
            $initialHtml = $parsed['html'];
            $initialCss = $parsed['css'];
            $initialJs = $parsed['js'];
        }
    } catch (Throwable $e) {
        error_log('Playground practice load error: ' . $e->getMessage());
    }
} elseif (isset($_GET['template'])) {
    $disableRestore = true;
    $template = (string) $_GET['template'];

    if ($template === 'button') {
        $pageTitle = 'Template: Button';
        $initialTitle = 'Button Starter';
        $initialHtml = '<button class="btn-primary">Click Me!</button>';
        $initialCss = ".btn-primary {\n  background: #667eea;\n  color: #fff;\n  border: none;\n  border-radius: 8px;\n  padding: 12px 20px;\n  font-weight: 600;\n  cursor: pointer;\n}\n\n.btn-primary:hover {\n  background: #5568d3;\n}";
        $initialJs = '';
    } elseif ($template === 'card') {
        $pageTitle = 'Template: Card';
        $initialTitle = 'Card Starter';
        $initialHtml = "<article class=\"card\">\n  <h2>Card Title</h2>\n  <p>This is a clean card component you can customize.</p>\n  <a href=\"#\">Learn more</a>\n</article>";
        $initialCss = ".card {\n  max-width: 360px;\n  padding: 20px;\n  border: 1px solid #e2e8f0;\n  border-radius: 12px;\n  box-shadow: 0 6px 20px rgba(0,0,0,0.08);\n}\n\n.card h2 { margin-top: 0; }";
        $initialJs = '';
    } elseif ($template === 'form') {
        $pageTitle = 'Template: Form';
        $initialTitle = 'Form Starter';
        $initialHtml = "<form class=\"contact-form\">\n  <label>Name</label>\n  <input type=\"text\" placeholder=\"Your name\">\n  <label>Email</label>\n  <input type=\"email\" placeholder=\"you@email.com\">\n  <button type=\"submit\">Send</button>\n</form>";
        $initialCss = ".contact-form {\n  max-width: 380px;\n  display: grid;\n  gap: 10px;\n}\n\n.contact-form input {\n  padding: 10px;\n  border: 1px solid #cbd5e1;\n  border-radius: 8px;\n}\n\n.contact-form button {\n  background: #667eea;\n  color: white;\n  border: 0;\n  border-radius: 8px;\n  padding: 10px;\n}";
        $initialJs = '';
    }
}

include 'includes/header.php';
?>

<div class="playground-shell">
    <section class="editor-area editor-area-full">
        <div class="playground-toolbar">
            <div class="playground-toolbar-left">
                <button id="pgRunBtn" class="btn btn-success btn-small" type="button">
                    <span class="material-icons">play_arrow</span>
                    Run
                </button>
                <button id="pgSaveBtn" class="btn btn-primary btn-small" type="button">
                    <span class="material-icons">save</span>
                    Save Project
                </button>
                <button id="pgLayoutBtn" class="btn btn-secondary btn-small" data-layout="bottom" type="button">
                    <span class="material-icons">view_week</span>
                    Output Right
                </button>
                <button id="pgFocusBtn" class="btn btn-secondary btn-small" type="button">
                    <span class="material-icons">fullscreen</span>
                    Focus Mode
                </button>
                <button id="pgResetBtn" class="btn btn-secondary btn-small" type="button">
                    <span class="material-icons">refresh</span>
                    Reset
                </button>
            </div>
            <div class="playground-toolbar-right">
                <input id="pgTitleInput" class="pg-title-input" type="text" maxlength="255" value="<?php echo htmlspecialchars($initialTitle); ?>" placeholder="Untitled Project">
                <?php if (!empty($challengeTag)): ?>
                    <span class="playground-tag"><?php echo htmlspecialchars($challengeTag); ?></span>
                <?php endif; ?>
                <span id="pgSaveStatus" class="playground-hint">Not saved</span>
            </div>
        </div>

        <div id="playgroundRoot" class="playground-root layout-bottom">
            <section id="pgEditorsWrap" class="pg-editors-wrap">
                <article class="pg-editor-card" id="pgHtmlPane">
                    <header class="pg-pane-header"><span class="pg-dot html"></span>HTML</header>
                    <textarea id="pgHtmlCode"><?php echo htmlspecialchars($initialHtml); ?></textarea>
                </article>
                <div class="pg-splitter pg-splitter-editor" data-editor-split-index="0" role="separator" tabindex="0" aria-label="Resize HTML and CSS panels"></div>
                <article class="pg-editor-card" id="pgCssPane">
                    <header class="pg-pane-header"><span class="pg-dot css"></span>CSS</header>
                    <textarea id="pgCssCode"><?php echo htmlspecialchars($initialCss); ?></textarea>
                </article>
                <div class="pg-splitter pg-splitter-editor" data-editor-split-index="1" role="separator" tabindex="0" aria-label="Resize CSS and JavaScript panels"></div>
                <article class="pg-editor-card" id="pgJsPane">
                    <header class="pg-pane-header"><span class="pg-dot js"></span>JavaScript</header>
                    <textarea id="pgJsCode"><?php echo htmlspecialchars($initialJs); ?></textarea>
                </article>
            </section>

            <div id="pgMainSplitter" class="pg-splitter pg-splitter-main" role="separator" tabindex="0" aria-label="Resize editor and output panels"></div>

            <section id="pgPreviewWrap" class="pg-preview-wrap">
                <header class="pg-pane-header"><span class="pg-dot output"></span>Live Output</header>
                <iframe id="pgPreviewFrame" sandbox="allow-scripts allow-modals" title="Live Playground Output"></iframe>
            </section>
        </div>
    </section>
</div>

<script>
window.PLAYGROUND_CONTEXT = {
    taskId: <?php echo $currentTaskId !== null ? (int) $currentTaskId : 'null'; ?>,
    practiceId: <?php echo $currentPracticeId !== null ? (int) $currentPracticeId : 'null'; ?>,
    disableRestore: <?php echo $disableRestore ? 'true' : 'false'; ?>
};
</script>

<?php include 'includes/footer.php'; ?>
