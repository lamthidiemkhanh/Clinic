<?php
class Database {
    private static ?PDO $pdo = null;

    public static function pdo(): PDO {
        if (self::$pdo instanceof PDO) return self::$pdo;
        // Reuse existing db.php config/connection if present
        $rootDb = __DIR__ . '/../../db.php';
        if (is_file($rootDb)) {
            require $rootDb; // expects $conn
            if (isset($conn) && $conn instanceof PDO) {
                self::$pdo = $conn;
                return self::$pdo;
            }
        }
        // Fallback minimal connection (adjust credentials as needed)
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $db   = getenv('DB_NAME') ?: 'clinic';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $dsn = "mysql:host={$host};dbname={$db};charset=utf8";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo = $pdo;
        return self::$pdo;
    }
}

