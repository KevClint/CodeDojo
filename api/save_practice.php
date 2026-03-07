<?php
/**
 * Save Practice API
 * Handles saving new practice submissions
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

function tableColumns(PDO $pdo, string $table): array
{
    $cols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (!empty($row['Field'])) {
            $cols[$row['Field']] = $row;
        }
    }
    return $cols;
}

function tableExists(PDO $pdo, string $table): bool
{
    $safeTable = $pdo->quote($table);
    $stmt = $pdo->query("SHOW TABLES LIKE {$safeTable}");
    return (bool) $stmt->fetchColumn();
}

function stringContains(string $haystack, string $needle): bool
{
    return $needle !== '' && strpos($haystack, $needle) !== false;
}

function fallbackValueForColumn(array $column)
{
    $type = strtolower((string) ($column['Type'] ?? ''));

    if (stringContains($type, 'int') || stringContains($type, 'decimal') || stringContains($type, 'float') || stringContains($type, 'double')) {
        return 0;
    }

    if (stringContains($type, 'bool') || stringContains($type, 'tinyint(1)')) {
        return 0;
    }

    return '';
}

function buildMarkerFromSources(string $html, string $css, string $js): string
{
    $payload = base64_encode(json_encode([
        'html' => $html,
        'css' => $css,
        'js' => $js
    ], JSON_UNESCAPED_UNICODE));

    return "<!-- CODEDOJO_PEN_V1:{$payload} -->";
}

try {
    $title = trim($_POST['title'] ?? '');
    $html_code = $_POST['html_code'] ?? '';
    $task_id = isset($_POST['task_id']) && $_POST['task_id'] !== '' ? intval($_POST['task_id']) : null;
    $practice_id = isset($_POST['practice_id']) && $_POST['practice_id'] !== '' ? intval($_POST['practice_id']) : null;
    $sourceHtml = isset($_POST['source_html']) ? (string) $_POST['source_html'] : '';
    $sourceCss = isset($_POST['source_css']) ? (string) $_POST['source_css'] : '';
    $sourceJs = isset($_POST['source_js']) ? (string) $_POST['source_js'] : '';

    if ($sourceHtml !== '' || $sourceCss !== '' || $sourceJs !== '') {
        $html_code = buildMarkerFromSources($sourceHtml, $sourceCss, $sourceJs);
    }

    if ($title === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Title is required'
        ]);
        exit;
    }

    if ($html_code === '') {
        echo json_encode([
            'success' => false,
            'message' => 'HTML code is required'
        ]);
        exit;
    }

    $pdo = getDBConnection();
    $practiceCols = tableColumns($pdo, 'user_practice');
    $userId = (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) ? (int) $_SESSION['user_id'] : null;
    $hasUsersTable = tableExists($pdo, 'users');
    $hasTasksTable = tableExists($pdo, 'practice_tasks');

    if ($userId !== null && isset($practiceCols['user_id'])) {
        if ($hasUsersTable) {
            $userCheck = $pdo->prepare('SELECT 1 FROM users WHERE id = :id LIMIT 1');
            $userCheck->execute([':id' => $userId]);
            if (!$userCheck->fetchColumn()) {
                $userId = null;
            }
        } else {
            $userId = null;
        }
    }

    if (isset($practiceCols['html_code'])) {
        $htmlType = strtolower((string) ($practiceCols['html_code']['Type'] ?? ''));
        if ($htmlType === 'text') {
            try {
                $pdo->exec('ALTER TABLE user_practice MODIFY html_code MEDIUMTEXT NOT NULL');
                $practiceCols = tableColumns($pdo, 'user_practice');
            } catch (Throwable $e) {
                error_log('Save Practice Warning (alter html_code): ' . $e->getMessage());
            }
        }
    }

    if ($task_id !== null && isset($practiceCols['task_id'])) {
        if ($hasTasksTable) {
            $taskCheck = $pdo->prepare('SELECT COUNT(*) FROM practice_tasks WHERE id = :id');
            $taskCheck->execute([':id' => $task_id]);
            if ((int) $taskCheck->fetchColumn() === 0) {
                $task_id = null;
            }
        } else {
            $task_id = null;
        }
    }

    $isUpdate = ($practice_id !== null && $practice_id > 0);

    if ($isUpdate) {
        $setParts = [];
        $params = [':id' => $practice_id];

        if (isset($practiceCols['user_id']) && $userId !== null) {
            $setParts[] = 'user_id = :user_id';
            $params[':user_id'] = $userId;
        }
        if (isset($practiceCols['title'])) {
            $setParts[] = 'title = :title';
            $params[':title'] = $title;
        }
        if (isset($practiceCols['html_code'])) {
            $setParts[] = 'html_code = :html_code';
            $params[':html_code'] = $html_code;
        }
        if (isset($practiceCols['task_id'])) {
            $setParts[] = 'task_id = :task_id';
            $params[':task_id'] = $task_id;
        }

        if (empty($setParts)) {
            throw new RuntimeException('No compatible columns for update');
        }

        $sql = 'UPDATE user_practice SET ' . implode(', ', $setParts) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $insertId = $practice_id;
    } else {
        $cols = [];
        $placeholders = [];
        $params = [];

        if (isset($practiceCols['user_id'])) {
            $cols[] = 'user_id';
            $placeholders[] = ':user_id';
            $params[':user_id'] = $userId;
        }
        if (isset($practiceCols['title'])) {
            $cols[] = 'title';
            $placeholders[] = ':title';
            $params[':title'] = $title;
        }
        if (isset($practiceCols['html_code'])) {
            $cols[] = 'html_code';
            $placeholders[] = ':html_code';
            $params[':html_code'] = $html_code;
        }
        if (isset($practiceCols['task_id'])) {
            $cols[] = 'task_id';
            $placeholders[] = ':task_id';
            $params[':task_id'] = $task_id;
        }
        if (isset($practiceCols['is_completed'])) {
            $cols[] = 'is_completed';
            $placeholders[] = ':is_completed';
            $params[':is_completed'] = 0;
        }

        foreach ($practiceCols as $name => $meta) {
            if (
                in_array($name, $cols, true) ||
                $name === 'id' ||
                $name === 'created_at' ||
                $name === 'updated_at'
            ) {
                continue;
            }

            $nullable = strtoupper((string) ($meta['Null'] ?? 'YES')) === 'YES';
            $hasDefault = array_key_exists('Default', $meta) && $meta['Default'] !== null;
            $extra = strtolower((string) ($meta['Extra'] ?? ''));
            $isAuto = stringContains($extra, 'auto_increment');

            if ($isAuto || $hasDefault) {
                continue;
            }

            $cols[] = $name;
            $placeholders[] = ':' . $name;
            $params[':' . $name] = $nullable ? null : fallbackValueForColumn($meta);
        }

        if (empty($cols) || !isset($practiceCols['title']) || !isset($practiceCols['html_code'])) {
            throw new RuntimeException('user_practice schema missing required columns');
        }

        $sql = 'INSERT INTO user_practice (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $insertId = $pdo->lastInsertId();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Project saved successfully',
        'id' => $insertId
    ]);
} catch (Throwable $e) {
    error_log('Save Practice Error: ' . $e->getMessage());

    $message = 'Database error occurred';
    $errorText = strtolower($e->getMessage());
    if (stringContains($errorText, 'data too long')) {
        $message = 'Saved code is too large for current database column. Please re-run fresh SQL.';
    }

    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}
?>
