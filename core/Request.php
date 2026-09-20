<?php
/**
 * Core HTTP Request Helper & Sanitizer
 */

defined('APP_INIT') or define('APP_INIT', true);

class Request {
    /**
     * Get sanitized GET parameter
     */
    public static function get(string $key, $default = null) {
        if (!isset($_GET[$key])) {
            return $default;
        }
        return self::sanitize($_GET[$key]);
    }

    /**
     * Get sanitized POST parameter
     */
    public static function post(string $key, $default = null) {
        if (!isset($_POST[$key])) {
            return $default;
        }
        return self::sanitize($_POST[$key]);
    }

    /**
     * Get raw POST/JSON body as associative array
     */
    public static function json(): array {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? self::sanitize($data) : [];
    }

    /**
     * Get all inputs combined
     */
    public static function all(): array {
        $data = array_merge($_GET, $_POST);
        return self::sanitize($data);
    }

    /**
     * Recursive Sanitization
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            $cleaned = [];
            foreach ($input as $k => $v) {
                $cleaned[htmlspecialchars(strip_tags((string)$k), ENT_QUOTES, 'UTF-8')] = self::sanitize($v);
            }
            return $cleaned;
        }
        if (is_string($input)) {
            return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
        }
        return $input;
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }

    /**
     * CSRF Token Generator & Verifier
     */
    public static function csrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * Handle File Upload with validation
     */
    public static function uploadFile(string $inputKey, string $targetSubDir, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xlsx']): array {
        if (!isset($_FILES[$inputKey]) || $_FILES[$inputKey]['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'No file uploaded or upload error code: ' . ($_FILES[$inputKey]['error'] ?? 'NONE')];
        }

        $file = $_FILES[$inputKey];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions, true)) {
            return ['success' => false, 'error' => 'File extension not permitted. Allowed: ' . implode(', ', $allowedExtensions)];
        }

        // Limit size: 10MB default
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'File size exceeds 10MB maximum limit.'];
        }

        $targetDir = ROOT_PATH . '/uploads/' . trim($targetSubDir, '/');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = uniqid('doc_', true) . '.' . $ext;
        $destination = $targetDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file['name'],
                'file_path' => 'uploads/' . trim($targetSubDir, '/') . '/' . $filename,
                'file_size' => $file['size'],
                'mime_type' => $file['type']
            ];
        }

        return ['success' => false, 'error' => 'Failed to save uploaded file.'];
    }
}
