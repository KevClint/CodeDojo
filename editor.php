<?php
// Backward compatibility route: existing editor links now use the live playground.
$query = $_SERVER['QUERY_STRING'] ?? '';
$target = 'live_playground.php' . ($query !== '' ? ('?' . $query) : '');
header('Location: ' . $target, true, 302);
exit;
