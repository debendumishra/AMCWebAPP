<?php
/**
 * Database Singleton Connection Handler
 */

defined('APP_INIT') or define('APP_INIT', true);

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Get active PDO instance
     */
    public static function getInstance(): ?PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // If database doesn't exist yet or connection fails, log error
                error_log("Database Connection Error: " . $e->getMessage());
                return null;
            }
        }
        return self::$instance;
    }

    /**
     * Test raw connection without specific db name (useful during /install)
     */
    public static function testConnection(string $host, string $port, string $user, string $pass, ?string $dbname = null): array {
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            if (!empty($dbname)) {
                $dsn .= ";dbname={$dbname}";
            }
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            return ['success' => true, 'pdo' => $pdo];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
