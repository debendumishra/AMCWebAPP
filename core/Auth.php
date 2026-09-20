<?php
/**
 * Core Authentication & RBAC Session Manager
 */

defined('APP_INIT') or define('APP_INIT', true);

class Auth {
    /**
     * Start secure session
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_set_cookie_params([
                    'lifetime' => defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 86400,
                    'path'     => '/',
                    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            @session_start();
        }
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool {
        self::init();
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get logged-in user ID
     */
    public static function id(): ?int {
        self::init();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get logged-in user data
     */
    public static function user(): ?array {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get user role ID
     */
    public static function role(): ?int {
        self::init();
        if (isset($_SESSION['user']['role_id'])) {
            return (int)$_SESSION['user']['role_id'];
        }
        return null;
    }

    /**
     * Get user role name
     */
    public static function roleName(): string {
        self::init();
        return $_SESSION['user']['role_name'] ?? 'Guest';
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole(int $roleId): bool {
        $userRole = self::role();
        if ($userRole === null) {
            return false;
        }
        return (int)$userRole === (int)$roleId;
    }

    /**
     * Check if user has any of the given roles
     */
    public static function hasAnyRole(array $roleIds): bool {
        $userRole = self::role();
        if ($userRole === null) {
            return false;
        }
        foreach ($roleIds as $r) {
            if ((int)$r === (int)$userRole) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set logged-in user session
     */
    public static function login(array $user): void {
        self::init();
        @session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user'] = [
            'id'          => (int)$user['id'],
            'name'        => $user['name'],
            'email'       => $user['email'],
            'mobile'      => $user['mobile'] ?? '',
            'role_id'     => (int)$user['role_id'],
            'role_name'   => $user['role_name'] ?? '',
            'customer_id' => !empty($user['customer_id']) ? (int)$user['customer_id'] : null,
            'avatar'      => $user['avatar'] ?? null
        ];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            @session_start();
        }
    }

    /**
     * Log user out and destroy session
     */
    public static function logout(): void {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        @session_destroy();
    }
}
