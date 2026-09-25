<?php
// config/database.php

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                if (DB_DRIVER === 'mysql') {
                    $dsn = sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                        DB_HOST,
                        DB_PORT,
                        DB_DATABASE
                    );
                    self::$instance = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                } else {
                    // SQLite (Default)
                    $dir = dirname(SQLITE_FILE);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $dsn = 'sqlite:' . SQLITE_FILE;
                    self::$instance = new PDO($dsn, null, null, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    // Enable Foreign Keys in SQLite
                    self::$instance->exec('PRAGMA foreign_keys = ON;');
                }
            } catch (PDOException $e) {
                die('Database Connection Error: ' . htmlspecialchars($e->getMessage()));
            }
        }
        return self::$instance;
    }
}

function get_db(): PDO {
    return Database::getConnection();
}
