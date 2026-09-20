<?php
/**
 * System Setting Model
 */

defined('APP_INIT') or define('APP_INIT', true);

class Setting extends Model {
    protected string $table = 'system_settings';

    private static ?array $cache = null;

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null) {
        if (self::$cache === null) {
            self::loadCache();
        }
        return self::$cache[$key] ?? $default;
    }

    /**
     * Set / Update a setting value
     */
    public static function set(string $key, string $value, string $group = 'general'): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_at)
            VALUES (:key, :val, :group, NOW())
            ON DUPLICATE KEY UPDATE setting_value = :val2, updated_at = NOW()
        ");
        $success = $stmt->execute([
            'key'    => $key,
            'val'    => $value,
            'group'  => $group,
            'val2'   => $value
        ]);

        if ($success) {
            if (self::$cache !== null) {
                self::$cache[$key] = $value;
            }
        }
        return $success;
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAll(): array {
        if (self::$cache === null) {
            self::loadCache();
        }
        return self::$cache;
    }

    /**
     * Preload all settings into static cache
     */
    private static function loadCache(): void {
        $db = Database::getInstance();
        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
            self::$cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Throwable $e) {
            self::$cache = [];
        }
    }
}
