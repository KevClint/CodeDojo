<?php
/**
 * CodeDojo - Code Editor Page
 * Main coding interface with live preview
 */

require_once 'config/database.php';

$pageTitle = 'Code Editor';
$currentPage = 'editor';
$includeEditor = true;

// Check if loading a specific task
$task = null;
$starterCode = "<!-- Write your HTML code here -->\n\n";

if (isset($_GET['task'])) {
    try {
        $pdo = getDBConnection();
        $taskId = intval($_GET['task']);
        
        $sql = "SELECT pt.*, l.title as lesson_title, l.difficulty 
                FROM practice_tasks pt 
                LEFT JOIN lessons l ON pt.lesson_id = l.id 
                WHERE pt.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $taskId]);
        $task = $stmt->fetch();
        
        if ($task && !empty($task['starter_code'])) {
            $starterCode = $task['starter_code'];
        }
    } catch (Exception $e) {
        error_log("Error loading task: " . $e->getMessage());
    }
}

// Check if loading a saved practice
elseif (isset($_GET['practice'])) {
    try {
        $pdo = getDBConnection();
        $practiceId = intval($_GET['practice']);
        
        $sql = "SELECT * FROM user_practice WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $practiceId]);
        $practice = $stmt->fetch();
        
        if ($practice) {
            $starterCode = $practice['html_code'];
            $pageTitle = 'Edit: ' . $practice['title'];
        }
    } catch (Exception $e) {
        error_log("Error loading practice: " . $e->getMessage());
    }
}

// Check if loading a template
elseif (isset($_GET['template'])) {
    $template = $_GET['template'];
    
    switch ($template) {
        case 'button':
            $starterCode = "<!-- Button Template -->\n<button style=\"\n  background-color: #667eea;\n  color: white;\n  padding: 12px 24px;\n  border: none;\n  border-radius: 8px;\n  font-size: 16px;\n  font-weight: 600;\n  cursor: pointer;\n  transition: all 0.3s ease;\n\">\n  Click Me!\n</button>\n\n<style>\n  button:hover {\n    background-color: #5568d3;\n    transform: translateY(-2px);\n    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);\n  }\n</style>";
            $pageTitle = 'Template: Button';
            break;
            
        case 'card':
            $starterCode = "<!-- Card Template -->\n<div style=\"\n  max-width: 400px;\n  background: white;\n  border-radius: 12px;\n  padding: 24px;\n  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\n  border: 1px solid #e2e8f0;\n\">\n  <h2 style=\"\n    margin: 0 0 16px 0;\n    font-size: 24px;\n    color: #1e293b;\n  \">\n    Card Title\n  </h2>\n  \n  <p style=\"\n    margin: 0 0 16px 0;\n    color: #64748b;\n    line-height: 1.6;\n  \">\n    This is a beautiful card component. You can add any content here like text, images, or buttons.\n  </p>\n  \n  <button style=\"\n    background-color: #667eea;\n    color: white;\n    padding: 10px 20px;\n    border: none;\n    border-radius: 6px;\n    cursor: pointer;\n    font-weight: 500;\n  \">\n    Learn More\n  </button>\n</div>";
            $pageTitle = 'Template: Card';
            break;
            
        case 'form':
            $starterCode = "<!-- Form Template -->\n<form style=\"\n  max-width: 500px;\n  background: white;\n  padding: 32px;\n  border-radius: 12px;\n  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\n\">\n  <h2 style=\"margin: 0 0 24px 0; color: #1e293b;\">\n    Contact Form\n  </h2>\n  \n  <div style=\"margin-bottom: 20px;\">\n    <label style=\"\n      display: block;\n      margin-bottom: 8px;\n      font-weight: 600;\n      color: #1e293b;\n    \">\n      Name\n    </label>\n    <input type=\"text\" placeholder=\"Your name\" style=\"\n      width: 100%;\n      padding: 12px;\n      border: 1px solid #e2e8f0;\n      border-radius: 8px;\n      font-size: 16px;\n    \">\n  </div>\n  \n  <div style=\"margin-bottom: 20px;\">\n    <label style=\"\n      display: block;\n      margin-bottom: 8px;\n      font-weight: 600;\n      color: #1e293b;\n    \">\n      Email\n    </label>\n    <input type=\"email\" placeholder=\"your@email.com\" style=\"\n      width: 100%;\n      padding: 12px;\n      border: 1px solid #e2e8f0;\n      border-radius: 8px;\n      font-size: 16px;\n    \">\n  </div>\n  \n  <div style=\"margin-bottom: 20px;\">\n    <label style=\"\n      display: block;\n      margin-bottom: 8px;\n      font-weight: 600;\n      color: #1e293b;\n    \">\n      Message\n    </label>\n    <textarea placeholder=\"Your message\" rows=\"4\" style=\"\n      width: 100%;\n      padding: 12px;\n      border: 1px solid #e2e8f0;\n      border-radius: 8px;\n      font-size: 16px;\n      resize: vertical;\n    \"></textarea>\n  </div>\n  \n  <button type=\"submit\" style=\"\n    width: 100%;\n    background-color: #667eea;\n    color: white;\n    padding: 12px;\n    border: none;\n    border-radius: 8px;\n    font-size: 16px;\n    font-weight: 600;\n    cursor: pointer;\n  \">\n    Send Message\n  </button>\n</form>";
            $pageTitle = 'Template: Form';
            break;
    }
}

include 'includes/header.php';
?>

<?php if ($task): ?>
    <!-- Task Panel -->
    <div class="task-panel">
        <div class="task-header">
            <h2 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h2>
            <span class="task-difficulty <?php echo htmlspecialchars($task['difficulty']); ?>">
                <?php echo htmlspecialchars($task['difficulty']); ?>
            </span>
        </div>
        
        <p class="task-instruction">
            <?php echo htmlspecialchars($task['instruction']); ?>
        </p>
        
        <?php if (!empty($task['hint'])): ?>
            <div class="hint-section">
                <div class="hint-header">
                    <span class="material-icons">lightbulb</span>
                    <span>Need a hint?</span>
                </div>
                <div class="hint-content">
                    <?php echo htmlspecialchars($task['hint']); ?>
                </div>
                <button id="showHintBtn" class="btn btn-secondary btn-small mt-1">
                    <span class="material-icons">lightbulb</span>
                    Show Hint
                </button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Editor Container -->
<div class="editor-container">
    <!-- Code Editor Panel -->
    <div class="editor-panel">
        <div class="editor-header">
            <span class="editor-title">HTML Editor</span>
            <div class="editor-actions">
                <button id="runBtn" class="btn btn-success btn-small">
                    <span class="material-icons">play_arrow</span>
                    Run
                </button>
                <button id="resetBtn" class="btn btn-secondary btn-small">
                    <span class="material-icons">refresh</span>
                    Reset
                </button>
                <button id="saveBtn" class="btn btn-primary btn-small">
                    <span class="material-icons">save</span>
                    Save
                </button>
            </div>
        </div>
        
        <div class="code-editor">
            <textarea id="htmlCode" spellcheck="false" autocomplete="off"><?php echo htmlspecialchars($starterCode); ?></textarea>
        </div>
    </div>
    
    <!-- Preview Panel -->
    <div class="preview-panel" id="previewPanel">
        <div class="editor-header">
            <span class="editor-title">Live Preview</span>
            <div class="editor-actions">
                <span style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                    <span class="material-icons" style="font-size: 16px;">info</span>
                    Press Ctrl+Enter to run
                </span>
                <button id="fullscreenBtn" class="btn btn-secondary btn-small">
                    <span class="material-icons">fullscreen</span>
                </button>
            </div>
        </div>
        
        <div class="preview-content">
            <iframe id="preview" title="HTML Preview"></iframe>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
