<?php

function loadConfig()
{
    $configPath = __DIR__ . '/config.local.php';

    if (!file_exists($configPath)) {
        exit('ConfigError: config.local.php が見つかりません。config.example.php をコピーして設定してください。');
    }

    $config = require $configPath;
    $requiredKeys = ['db_dsn', 'db_user', 'db_pass'];

    foreach ($requiredKeys as $key) {
        if (!isset($config[$key]) || trim((string)$config[$key]) === '') {
            exit('ConfigError: ' . $key . ' が未設定です。');
        }
    }

    return $config;
}

function connectDb()
{
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    $config = loadConfig();

    try {
        $pdo = new PDO(
            $config['db_dsn'],
            $config['db_user'],
            $config['db_pass']
        );
    } catch (PDOException $e) {
        exit('DBConnectError:' . $e->getMessage());
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS gs_reading_log (
                id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(128) NOT NULL,
                author VARCHAR(128) NOT NULL,
                cover_image TEXT,
                status VARCHAR(32) NOT NULL,
                memo TEXT,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );

        ensureColumnExists($pdo, 'gs_reading_log', 'cover_image', 'TEXT');
    } catch (PDOException $e) {
        exit('DBCreateError:' . $e->getMessage());
    }

    return $pdo;
}

function ensureColumnExists($pdo, $tableName, $columnName, $columnDefinition)
{
    $sql = "SHOW COLUMNS FROM {$tableName} LIKE :column_name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':column_name', $columnName, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetch(PDO::FETCH_ASSOC) !== false) {
        return;
    }

    $pdo->exec("ALTER TABLE {$tableName} ADD {$columnName} {$columnDefinition}");
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function isValidHttpUrl($value)
{
    if ($value === '') {
        return true;
    }

    return filter_var($value, FILTER_VALIDATE_URL) !== false
        && preg_match('/^https?:\/\//', $value) === 1;
}
