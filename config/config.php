<?php
/**
 * Application Configuration
 * Computer AMC, Field Service, Inventory, Billing & Customer Management System
 */

// Prevent direct access
defined('APP_INIT') or define('APP_INIT', true);

// Application Environment: 'development' or 'production'
define('APP_ENV', 'development');

// Base URLs and Paths
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . $host . $scriptDir, '/');

// Fix base URL if pointing to root or subfolder
define('BASE_URL', $baseUrl);
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'amc_webapp_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Company & System Defaults (Overridable via system_settings table)
define('APP_NAME', 'ServicEngine AMC');
define('APP_VERSION', '1.0.0');
define('DEFAULT_TIMEZONE', 'Asia/Kolkata');
define('DEFAULT_CURRENCY', '₹');
define('SESSION_LIFETIME', 86400); // 24 hours

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Set Default Timezone
date_default_timezone_set(DEFAULT_TIMEZONE);
