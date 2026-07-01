<?php

function connectDb()
{
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    try {
        $pdo = new PDO(
            'mysql:dbname=limejackal58_readinglog;charset=utf8;host=mysql3116.db.sakura.ne.jp',
            'limejackal58_readinglog',
            'reading_2026'
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
                status VARCHAR(32) NOT NULL,
                memo TEXT,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    } catch (PDOException $e) {
        exit('DBCreateError:' . $e->getMessage());
    }

    return $pdo;
}

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
