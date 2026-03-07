<?php
/**
 * CodeDojo - Progress and Auto-Grading Helpers
 */

require_once __DIR__ . '/database.php';

function ensureProgressSchema(PDO $pdo): void {
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_task_progress (
            user_id INT NOT NULL,
            task_id INT NOT NULL,
            attempts INT NOT NULL DEFAULT 0,
            passes INT NOT NULL DEFAULT 0,
            best_score INT NOT NULL DEFAULT 0,
            last_attempt_at TIMESTAMP NULL DEFAULT NULL,
            completed_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, task_id),
            INDEX idx_task_progress_task (task_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_daily_activity (
            user_id INT NOT NULL,
            activity_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, activity_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_lesson_badges (
            user_id INT NOT NULL,
            lesson_id INT NOT NULL,
            awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, lesson_id),
            INDEX idx_badge_lesson (lesson_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    ensureColumnExists($pdo, 'practice_tasks', 'grading_rules', 'TEXT NULL AFTER solution_code');
    ensureColumnExists($pdo, 'user_practice', 'user_id', 'INT NULL AFTER id');

    $initialized = true;
}

function ensureColumnExists(PDO $pdo, string $table, string $column, string $definition): void {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([
        ':table_name' => $table,
        ':column_name' => $column
    ]);
    $exists = (int) $stmt->fetchColumn() > 0;

    if (!$exists) {
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
    }
}

function getActiveUserId(): ?int {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $role = $_SESSION['role'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;
    if ($role !== 'user' || !is_numeric($userId)) {
        return null;
    }
    return (int) $userId;
}

function buildTaskRules(array $task): array {
    if (!empty($task['grading_rules'])) {
        $decoded = json_decode($task['grading_rules'], true);
        if (is_array($decoded) && !empty($decoded['checks']) && is_array($decoded['checks'])) {
            return $decoded;
        }
    }

    return inferTaskRules($task);
}

function inferTaskRules(array $task): array {
    $title = strtolower((string) ($task['title'] ?? ''));

    $rulesByTitle = [
        'create your first button' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'button', 'min' => 1, 'message' => 'Add a <button> element'],
                ['type' => 'text_contains', 'text' => 'Click Me!', 'message' => 'Button should include "Click Me!"'],
                ['type' => 'attribute', 'tag' => 'button', 'attr' => 'style', 'min' => 1, 'message' => 'Style your button with inline CSS']
            ]
        ],
        'build a simple card' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'div', 'min' => 1, 'message' => 'Add a container <div>'],
                ['type' => 'element_any', 'tags' => ['h1', 'h2', 'h3'], 'min' => 1, 'message' => 'Add a heading inside the card'],
                ['type' => 'element', 'tag' => 'p', 'min' => 1, 'message' => 'Add a paragraph inside the card']
            ]
        ],
        'heading hierarchy' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'h1', 'min' => 1, 'message' => 'Add an <h1> heading'],
                ['type' => 'element', 'tag' => 'h2', 'min' => 1, 'message' => 'Add an <h2> heading'],
                ['type' => 'element', 'tag' => 'h3', 'min' => 1, 'message' => 'Add an <h3> heading']
            ]
        ],
        'format a paragraph' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'p', 'min' => 1, 'message' => 'Add a <p> paragraph'],
                ['type' => 'element', 'tag' => 'strong', 'min' => 1, 'message' => 'Make one word bold with <strong>'],
                ['type' => 'element', 'tag' => 'em', 'min' => 1, 'message' => 'Make one word italic with <em>']
            ]
        ],
        'create a hyperlink' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'a', 'min' => 1, 'message' => 'Add an <a> link'],
                ['type' => 'attribute_contains', 'tag' => 'a', 'attr' => 'href', 'needle' => 'google.com', 'min' => 1, 'message' => 'Set href to Google URL'],
                ['type' => 'attribute_equals', 'tag' => 'a', 'attr' => 'target', 'value' => '_blank', 'min' => 1, 'message' => 'Open the link in a new tab']
            ]
        ],
        'add an image' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'img', 'min' => 1, 'message' => 'Add an <img> element'],
                ['type' => 'attribute', 'tag' => 'img', 'attr' => 'src', 'min' => 1, 'message' => 'Provide an image src'],
                ['type' => 'attribute', 'tag' => 'img', 'attr' => 'alt', 'min' => 1, 'message' => 'Provide accessible alt text']
            ]
        ],
        'build an unordered list' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'ul', 'min' => 1, 'message' => 'Add an unordered list <ul>'],
                ['type' => 'element', 'tag' => 'li', 'min' => 5, 'message' => 'Add at least 5 list items <li>']
            ]
        ],
        'create a simple table' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'table', 'min' => 1, 'message' => 'Add a <table>'],
                ['type' => 'element', 'tag' => 'th', 'min' => 3, 'message' => 'Add at least 3 header cells <th>'],
                ['type' => 'element', 'tag' => 'tr', 'min' => 4, 'message' => 'Add a header row plus 3 data rows']
            ]
        ],
        'build a contact form' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'form', 'min' => 1, 'message' => 'Add a <form>'],
                ['type' => 'attribute_equals', 'tag' => 'input', 'attr' => 'type', 'value' => 'text', 'min' => 1, 'message' => 'Add a text input for name'],
                ['type' => 'attribute_equals', 'tag' => 'input', 'attr' => 'type', 'value' => 'email', 'min' => 1, 'message' => 'Add an email input'],
                ['type' => 'element_any', 'tags' => ['button', 'input'], 'min' => 1, 'message' => 'Add a submit control']
            ]
        ],
        'semantic article structure' => [
            'checks' => [
                ['type' => 'element', 'tag' => 'article', 'min' => 1, 'message' => 'Add an <article>'],
                ['type' => 'element', 'tag' => 'header', 'min' => 1, 'message' => 'Add a <header>'],
                ['type' => 'element', 'tag' => 'main', 'min' => 1, 'message' => 'Add a <main>'],
                ['type' => 'element', 'tag' => 'footer', 'min' => 1, 'message' => 'Add a <footer>']
            ]
        ]
    ];

    foreach ($rulesByTitle as $needle => $rules) {
        if (strpos($title, $needle) !== false) {
            return $rules;
        }
    }

    return [
        'checks' => [
            ['type' => 'text_length', 'min' => 20, 'message' => 'Write at least 20 characters of HTML code']
        ]
    ];
}

function gradeTaskSubmission(string $htmlCode, array $rules): array {
    $checks = $rules['checks'] ?? [];
    if (empty($checks)) {
        return [
            'passed' => false,
            'score' => 0,
            'checks' => []
        ];
    }

    $dom = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $htmlCode, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($internalErrors);

    $results = [];
    $passedCount = 0;

    foreach ($checks as $check) {
        $result = evaluateSingleCheck($dom, $htmlCode, $check);
        if ($result['passed']) {
            $passedCount++;
        }
        $results[] = $result;
    }

    $totalChecks = count($checks);
    $score = (int) round(($passedCount / $totalChecks) * 100);

    return [
        'passed' => $passedCount === $totalChecks,
        'score' => $score,
        'checks' => $results
    ];
}

function evaluateSingleCheck(DOMDocument $dom, string $htmlCode, array $check): array {
    $type = (string) ($check['type'] ?? '');
    $min = isset($check['min']) ? (int) $check['min'] : 1;
    $message = (string) ($check['message'] ?? 'Requirement check');
    $actual = 0;

    if ($type === 'element') {
        $tag = strtolower((string) ($check['tag'] ?? ''));
        if ($tag !== '') {
            $actual = $dom->getElementsByTagName($tag)->length;
        }
        return [
            'type' => $type,
            'message' => $message,
            'passed' => $actual >= $min,
            'expected' => $min,
            'actual' => $actual
        ];
    }

    if ($type === 'element_any') {
        $tags = is_array($check['tags'] ?? null) ? $check['tags'] : [];
        foreach ($tags as $tag) {
            $actual += $dom->getElementsByTagName(strtolower((string) $tag))->length;
        }
        return [
            'type' => $type,
            'message' => $message,
            'passed' => $actual >= $min,
            'expected' => $min,
            'actual' => $actual
        ];
    }

    if ($type === 'attribute' || $type === 'attribute_equals' || $type === 'attribute_contains') {
        $tag = strtolower((string) ($check['tag'] ?? '*'));
        $attr = strtolower((string) ($check['attr'] ?? ''));
        $expectedValue = strtolower((string) ($check['value'] ?? ''));
        $needle = strtolower((string) ($check['needle'] ?? ''));

        $nodes = $tag === '*' ? $dom->getElementsByTagName('*') : $dom->getElementsByTagName($tag);
        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement || !$node->hasAttribute($attr)) {
                continue;
            }

            $value = strtolower((string) $node->getAttribute($attr));
            if ($type === 'attribute' ||
                ($type === 'attribute_equals' && $value === $expectedValue) ||
                ($type === 'attribute_contains' && $needle !== '' && strpos($value, $needle) !== false)) {
                $actual++;
            }
        }

        return [
            'type' => $type,
            'message' => $message,
            'passed' => $actual >= $min,
            'expected' => $min,
            'actual' => $actual
        ];
    }

    if ($type === 'text_contains') {
        $needle = strtolower(trim((string) ($check['text'] ?? '')));
        $haystack = strtolower(strip_tags($htmlCode));
        $actual = ($needle !== '' && strpos($haystack, $needle) !== false) ? 1 : 0;
        return [
            'type' => $type,
            'message' => $message,
            'passed' => $actual >= 1,
            'expected' => 1,
            'actual' => $actual
        ];
    }

    if ($type === 'text_length') {
        $actual = strlen(trim($htmlCode));
        return [
            'type' => $type,
            'message' => $message,
            'passed' => $actual >= $min,
            'expected' => $min,
            'actual' => $actual
        ];
    }

    return [
        'type' => $type,
        'message' => $message,
        'passed' => false,
        'expected' => $min,
        'actual' => 0
    ];
}

function recordUserTaskAttempt(PDO $pdo, int $userId, int $taskId, bool $passed, int $score): void {
    $pdo->prepare("
        INSERT INTO user_task_progress (user_id, task_id, attempts, passes, best_score, last_attempt_at, completed_at)
        VALUES (:user_id, :task_id, 1, :passes, :best_score, NOW(), :completed_at)
        ON DUPLICATE KEY UPDATE
            attempts = attempts + 1,
            passes = passes + VALUES(passes),
            best_score = GREATEST(best_score, VALUES(best_score)),
            last_attempt_at = NOW(),
            completed_at = CASE
                WHEN completed_at IS NULL AND VALUES(completed_at) IS NOT NULL THEN VALUES(completed_at)
                ELSE completed_at
            END
    ")->execute([
        ':user_id' => $userId,
        ':task_id' => $taskId,
        ':passes' => $passed ? 1 : 0,
        ':best_score' => max(0, min(100, $score)),
        ':completed_at' => $passed ? date('Y-m-d H:i:s') : null
    ]);

    recordUserDailyActivity($pdo, $userId);
}

function recordUserDailyActivity(PDO $pdo, int $userId): void {
    $pdo->prepare("
        INSERT IGNORE INTO user_daily_activity (user_id, activity_date)
        VALUES (:user_id, CURDATE())
    ")->execute([':user_id' => $userId]);
}

function calculateUserStreak(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("
        SELECT activity_date
        FROM user_daily_activity
        WHERE user_id = :user_id
        ORDER BY activity_date DESC
    ");
    $stmt->execute([':user_id' => $userId]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!$dates) {
        return 0;
    }

    $today = new DateTime('today');
    $yesterday = (clone $today)->modify('-1 day');
    $latest = new DateTime($dates[0]);

    if ($latest < $yesterday) {
        return 0;
    }

    $streak = 0;
    $expected = $latest;
    foreach ($dates as $dateStr) {
        $date = new DateTime($dateStr);
        if ($date->format('Y-m-d') !== $expected->format('Y-m-d')) {
            break;
        }
        $streak++;
        $expected->modify('-1 day');
    }

    return $streak;
}

function awardLessonBadges(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT
            l.id AS lesson_id,
            l.title AS lesson_title,
            COUNT(pt.id) AS total_tasks,
            SUM(CASE WHEN utp.completed_at IS NOT NULL THEN 1 ELSE 0 END) AS completed_tasks
        FROM lessons l
        LEFT JOIN practice_tasks pt ON pt.lesson_id = l.id
        LEFT JOIN user_task_progress utp ON utp.task_id = pt.id AND utp.user_id = :user_id
        GROUP BY l.id, l.title
        HAVING total_tasks > 0 AND completed_tasks = total_tasks
    ");
    $stmt->execute([':user_id' => $userId]);
    $masteredLessons = $stmt->fetchAll();

    $newBadges = [];
    $insert = $pdo->prepare("
        INSERT IGNORE INTO user_lesson_badges (user_id, lesson_id)
        VALUES (:user_id, :lesson_id)
    ");

    foreach ($masteredLessons as $lesson) {
        $insert->execute([
            ':user_id' => $userId,
            ':lesson_id' => (int) $lesson['lesson_id']
        ]);
        if ($insert->rowCount() > 0) {
            $newBadges[] = [
                'lesson_id' => (int) $lesson['lesson_id'],
                'lesson_title' => $lesson['lesson_title']
            ];
        }
    }

    return $newBadges;
}

function getUserProgressSnapshot(PDO $pdo, int $userId): array {
    $summary = [
        'completed_tasks' => 0,
        'total_attempts' => 0,
        'mastered_lessons' => 0,
        'streak_days' => calculateUserStreak($pdo, $userId)
    ];

    $stmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) AS completed_tasks,
            SUM(attempts) AS total_attempts
        FROM user_task_progress
        WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $stats = $stmt->fetch();
    if ($stats) {
        $summary['completed_tasks'] = (int) ($stats['completed_tasks'] ?? 0);
        $summary['total_attempts'] = (int) ($stats['total_attempts'] ?? 0);
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM user_lesson_badges WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $summary['mastered_lessons'] = (int) $stmt->fetchColumn();

    $taskRows = $pdo->prepare("
        SELECT task_id, attempts, passes, best_score, completed_at
        FROM user_task_progress
        WHERE user_id = :user_id
    ");
    $taskRows->execute([':user_id' => $userId]);
    $taskProgress = [];
    foreach ($taskRows->fetchAll() as $row) {
        $taskProgress[(int) $row['task_id']] = [
            'attempts' => (int) $row['attempts'],
            'passes' => (int) $row['passes'],
            'best_score' => (int) $row['best_score'],
            'is_completed' => !empty($row['completed_at'])
        ];
    }

    $badgesStmt = $pdo->prepare("
        SELECT lesson_id, awarded_at
        FROM user_lesson_badges
        WHERE user_id = :user_id
        ORDER BY awarded_at DESC
    ");
    $badgesStmt->execute([':user_id' => $userId]);
    $badges = $badgesStmt->fetchAll();

    return [
        'summary' => $summary,
        'task_progress' => $taskProgress,
        'badges' => $badges
    ];
}

