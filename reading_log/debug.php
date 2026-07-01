<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/functions.php';

echo "PHP OK\n";

try {
    $pdo = connectDb();
    echo "DB connection OK\n";
    $stmt = $pdo->query('SELECT 1');
    $result = $stmt->fetch();
    echo "DB query OK: " . ($result[0] ?? 'no result') . "\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
