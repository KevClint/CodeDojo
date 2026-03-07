<?php
require 'config/database.php';
try {
    $pdo = getDBConnection();
    echo "CONNECTED\n";
    foreach ($pdo->query('SHOW TABLES') as $r) {
        echo implode('|', $r) . "\n";
    }
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . "\n";
}
