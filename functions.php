<?php
// 共通ヘルパー
function connectDb(): PDO
{
    try {
        $pdo = new PDO('mysql:dbname=gs_db_class;charset=utf8mb4;host=localhost', 'root', '');
    } catch (PDOException $e) {
        exit('DBConnectError:' . $e->getMessage());
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS gs_bookmark_table (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(128) NOT NULL,
            url VARCHAR(255) NOT NULL,
            category VARCHAR(64) NOT NULL DEFAULT 'Other',
            memo TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    );

    return $pdo;
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
