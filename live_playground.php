<?php
/**
 * CodeDojo - Live Playground
 * CodePen-style playground with resizable editors and live preview
 */

require_once 'config/database.php';

$pageTitle = 'Live Playground';
$currentPage = 'editor';
$includePlayground = true;

$initialHtml = "<!DOCTYPE html>\n<html>\n<head>\n  <title>Live Playground</title>\n</head>\n<body>\n  <h1>Hello, CodeDojo</h1>\n  <p>Edit HTML/CSS/JS and see output below.</p>\n</body>\n</html>";
$initialCss = "body {\n  font-family: Arial, sans-serif;\n  padding: 24px;\n}\n\nh1 {\n  color: #667eea;\n}";
$initialJs = "console.log(\"Playground ready\");";
$currentTaskId = null;
$taskInfo = null;
$disableRestore = false;
$current_lesson = null;

if (isset($_GET['task'])) {
    $disableRestore = true;
    $currentTaskId = intval($_GET['task']);
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT pt.id, pt.title, pt.instruction, pt.hint, pt.starter_code, l.title AS lesson_title
            FROM practice_tasks pt
            LEFT JOIN lessons l ON pt.lesson_id = l.id
            WHERE pt.id = :id
        ");
        $stmt->execute([':id' => $currentTaskId]);
        $taskInfo = $stmt->fetch();
        if ($taskInfo) {
            $pageTitle = 'Task: ' . ($taskInfo['title'] ?? 'Playground');
            if (!empty($taskInfo['starter_code'])) {
                $initialHtml = $taskInfo['starter_code'];
            }
        }
    } catch (Throwable $e) {
        error_log('Playground task load error: ' . $e->getMessage());
    }
} elseif (isset($_GET['practice'])) {
    $disableRestore = true;
    $practiceId = intval($_GET['practice']);
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT title, html_code, task_id FROM user_practice WHERE id = :id");
        $stmt->execute([':id' => $practiceId]);
        $practice = $stmt->fetch();
        if ($practice) {
            $pageTitle = 'Edit: ' . ($practice['title'] ?? 'Practice');
            $initialHtml = $practice['html_code'] ?? $initialHtml;
            $currentTaskId = !empty($practice['task_id']) ? intval($practice['task_id']) : null;
        }
    } catch (Throwable $e) {
        error_log('Playground practice load error: ' . $e->getMessage());
    }
} elseif (isset($_GET['template'])) {
    $disableRestore = true;
    $template = (string) $_GET['template'];
    if ($template === 'button') {
        $pageTitle = 'Template: Button';
        $initialHtml = "<button class=\"btn-primary\">Click Me!</button>";
        $initialCss = ".btn-primary {\n  background: #667eea;\n  color: #fff;\n  border: none;\n  border-radius: 8px;\n  padding: 12px 20px;\n  font-weight: 600;\n  cursor: pointer;\n}\n\n.btn-primary:hover {\n  background: #5568d3;\n}";
        $initialJs = '';
    } elseif ($template === 'card') {
        $pageTitle = 'Template: Card';
        $initialHtml = "<article class=\"card\">\n  <h2>Card Title</h2>\n  <p>This is a clean card component you can customize.</p>\n  <a href=\"#\">Learn more</a>\n</article>";
        $initialCss = ".card {\n  max-width: 360px;\n  padding: 20px;\n  border: 1px solid #e2e8f0;\n  border-radius: 12px;\n  box-shadow: 0 6px 20px rgba(0,0,0,0.08);\n}\n\n.card h2 { margin-top: 0; }";
        $initialJs = '';
    } elseif ($template === 'form') {
        $pageTitle = 'Template: Form';
        $initialHtml = "<form class=\"contact-form\">\n  <label>Name</label>\n  <input type=\"text\" placeholder=\"Your name\">\n  <label>Email</label>\n  <input type=\"email\" placeholder=\"you@email.com\">\n  <button type=\"submit\">Send</button>\n</form>";
        $initialCss = ".contact-form {\n  max-width: 380px;\n  display: grid;\n  gap: 10px;\n}\n\n.contact-form input {\n  padding: 10px;\n  border: 1px solid #cbd5e1;\n  border-radius: 8px;\n}\n\n.contact-form button {\n  background: #667eea;\n  color: white;\n  border: 0;\n  border-radius: 8px;\n  padding: 10px;\n}";
        $initialJs = '';
    }
}

if (!empty($taskInfo)) {
    $defaultLesson = [
        'title' => 'Image Tags: Wanted Posters',
        'scenario' => 'You need to build a digital wanted poster for a notorious pirate making their way through the Grand Line. You have their name, but now you need to display their picture to the world!',
        'theory' => 'The <img> tag is self-closing. It uses the "src" attribute to point to the image link, and the "alt" attribute to describe the image.',
        'tasks' => [
            'Add an <img> tag below the pirate\'s name.',
            'Set the src attribute to "https://example.com/pirate.jpg".',
            'Set the alt attribute to "Pirate with a straw hat".'
        ],
        'hint' => 'Remember, since it is a self-closing tag, it looks like this: <img src="..." alt="..."> (No closing </img> needed!).'
    ];

    $instruction = trim((string) ($taskInfo['instruction'] ?? ''));
    $scenarioLines = [];
    $parsedTasks = [];

    if ($instruction !== '') {
        $lines = preg_split('/\r\n|\r|\n/', $instruction);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            if (preg_match('/^(?:[-*]|\d+[.)])\s+(.+)$/', $trimmed, $matches)) {
                $parsedTasks[] = $matches[1];
            } else {
                $scenarioLines[] = $trimmed;
            }
        }
    }

    $current_lesson = [
        'title' => (string) ($taskInfo['title'] ?? $defaultLesson['title']),
        'scenario' => !empty($scenarioLines) ? implode(' ', $scenarioLines) : $defaultLesson['scenario'],
        'theory' => 'Focus on valid HTML syntax and required attributes. Match the expected output exactly.',
        'tasks' => !empty($parsedTasks) ? array_slice($parsedTasks, 0, 4) : [
            'Read the scenario and identify the exact HTML element needed.',
            'Write the tag in the HTML editor with all required attributes.',
            'Run the preview and confirm your output matches the challenge.'
        ],
        'hint' => !empty($taskInfo['hint']) ? (string) $taskInfo['hint'] : $defaultLesson['hint']
    ];
}

if (empty($current_lesson)) {
    $current_lesson = [
        'title' => 'Lesson Mode',
        'scenario' => 'Choose a challenge to load instructions here. Your lesson guide will stay on the left while you code on the right.',
        'theory' => 'This panel is powered by PHP data, so each lesson can inject title, scenario, theory, tasks, and hints dynamically.',
        'tasks' => [
            'Open Lessons and launch a challenge.',
            'Read the scenario and complete each checkpoint.',
            'Use Run Tests to verify your solution.'
        ],
        'hint' => ''
    ];
}

$extraHead = '
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material-darker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/split.js/1.6.5/split.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
';

include 'includes/header.php';
?>

<div class="playground-shell">
    <div class="lesson-workspace">
        <aside class="lesson-panel">
            <div class="task-panel">
            <div class="task-header">
                <h2 class="task-title"><?php echo htmlspecialchars($current_lesson['title']); ?></h2>
                <span class="task-difficulty beginner"><?php echo htmlspecialchars($taskInfo['lesson_title'] ?? 'Task'); ?></span>
            </div>
            <div class="lesson-structure">
                <section class="lesson-part scenario-part">
                    <h3 class="lesson-label">The Hook</h3>
                    <p class="lesson-text"><?php echo htmlspecialchars($current_lesson['scenario']); ?></p>
                </section>

                <section class="lesson-part theory-part">
                    <h3 class="lesson-label">The Theory</h3>
                    <p class="lesson-text"><?php echo htmlspecialchars($current_lesson['theory']); ?></p>
                </section>

                <section class="lesson-part checkpoints-part">
                    <h3 class="lesson-label">Checkpoints</h3>
                    <ul class="lesson-checkpoints">
                        <?php foreach (($current_lesson['tasks'] ?? []) as $task): ?>
                            <li><?php echo htmlspecialchars($task); ?></li>
                        <?php endforeach; ?>
                        <?php if (empty($current_lesson['tasks'])): ?>
                            <li>No tasks yet.</li>
                        <?php endif; ?>
                    </ul>
                </section>

                <?php if (!empty($current_lesson['hint'])): ?>
                <details class="hint-reveal">
                    <summary>
                        <span class="material-icons" aria-hidden="true">lightbulb</span>
                        Hidden Hint
                    </summary>
                    <div class="hint-reveal-content"><?php echo htmlspecialchars($current_lesson['hint']); ?></div>
                </details>
                <?php endif; ?>
            </div>
            </div>
            <div class="lesson-panel-footer">
                <button id="lessonRunTests" class="btn btn-success lesson-run-tests" type="button">
                    <span class="material-icons">fact_check</span>
                    Run Tests
                </button>
            </div>
        </aside>

        <section class="editor-area">
            <div class="playground-toolbar">
                <div class="playground-toolbar-left">
                    <button id="pgRunBtn" class="btn btn-success btn-small">
                        <span class="material-icons">play_arrow</span>
                        Run
                    </button>
                    <button id="pgSaveBtn" class="btn btn-secondary btn-small">
                        <span class="material-icons">save</span>
                        Save
                    </button>
                    <button id="pgLayoutBtn" class="btn btn-secondary btn-small" data-layout="bottom">
                        <span class="material-icons">view_week</span>
                        Output Right
                    </button>
                    <button id="pgFocusBtn" class="btn btn-secondary btn-small">
                        <span class="material-icons">fullscreen</span>
                        Focus Mode
                    </button>
                    <button id="pgResetBtn" class="btn btn-secondary btn-small">
                        <span class="material-icons">refresh</span>
                        Reset
                    </button>
                </div>
                <div class="playground-toolbar-right">
                    <span class="playground-hint">Auto-run: 300ms debounce</span>
                </div>
            </div>

            <div id="playgroundRoot" class="playground-root layout-bottom">
                <section id="pgEditorsWrap" class="pg-editors-wrap">
                    <article class="pg-editor-card" id="pgHtmlPane">
                        <header class="pg-pane-header"><span class="pg-dot html"></span>HTML</header>
                        <textarea id="pgHtmlCode"><?php echo htmlspecialchars($initialHtml); ?></textarea>
                    </article>
                    <article class="pg-editor-card" id="pgCssPane">
                        <header class="pg-pane-header"><span class="pg-dot css"></span>CSS</header>
                        <textarea id="pgCssCode"><?php echo htmlspecialchars($initialCss); ?></textarea>
                    </article>
                    <article class="pg-editor-card" id="pgJsPane">
                        <header class="pg-pane-header"><span class="pg-dot js"></span>JavaScript</header>
                        <textarea id="pgJsCode"><?php echo htmlspecialchars($initialJs); ?></textarea>
                    </article>
                </section>

                <section id="pgPreviewWrap" class="pg-preview-wrap">
                    <header class="pg-pane-header"><span class="pg-dot output"></span>Live Output</header>
                    <iframe id="pgPreviewFrame" sandbox="allow-scripts allow-modals" title="Live Playground Output"></iframe>
                </section>
            </div>
        </section>
    </div>
</div>

<script>
window.PLAYGROUND_CONTEXT = {
    taskId: <?php echo $currentTaskId !== null ? (int) $currentTaskId : 'null'; ?>,
    disableRestore: <?php echo $disableRestore ? 'true' : 'false'; ?>
};

const lessonRunTestsBtn = document.getElementById('lessonRunTests');
if (lessonRunTestsBtn) {
    lessonRunTestsBtn.addEventListener('click', function () {
        const runBtn = document.getElementById('pgRunBtn');
        if (runBtn) {
            runBtn.click();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
