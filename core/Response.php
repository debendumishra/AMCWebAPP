<?php
/**
 * Core HTTP Response Helper
 */

defined('APP_INIT') or define('APP_INIT', true);

class Response {
    /**
     * Send JSON Response
     */
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send JSON Success
     */
    public static function success(string $message = 'Operation successful', array $data = []): void {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ]);
    }

    /**
     * Send JSON Error
     */
    public static function error(string $message = 'Operation failed', array $errors = [], int $statusCode = 400): void {
        self::json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ], $statusCode);
    }

    /**
     * Redirect to relative path or URL
     */
    public static function redirect(string $path): void {
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            $url = BASE_URL . '/' . ltrim($path, '/');
        } else {
            $url = $path;
        }
        header("Location: " . $url);
        exit;
    }
}
