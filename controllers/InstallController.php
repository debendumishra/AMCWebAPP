<?php
/**
 * Installation Wizard & Demo Data Manager Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

class InstallController extends Controller {
    public function index(): void {
        $dbStatus = false;
        $tablesCount = 0;
        $hasDemoData = false;

        $db = Database::getInstance();
        if ($db) {
            $dbStatus = true;
            try {
                $stmt = $db->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $tablesCount = count($tables);

                if ($tablesCount > 0) {
                    $uStmt = $db->query("SELECT COUNT(*) FROM users");
                    $userCount = (int)$uStmt->fetchColumn();
                    $hasDemoData = ($userCount > 0);
                }
            } catch (Exception $e) {
                // Table might not exist yet
            }
        }

        $this->render('install/index', [
            'dbStatus'     => $dbStatus,
            'tablesCount'  => $tablesCount,
            'hasDemoData'  => $hasDemoData,
            'phpVersion'   => PHP_VERSION,
            'extensions'   => [
                'pdo'       => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'json'      => extension_loaded('json'),
                'mbstring'  => extension_loaded('mbstring'),
                'gd'        => extension_loaded('gd')
            ]
        ], 'auth_layout');
    }

    /**
     * Run Schema Migration & Initial Seed
     */
    public function runMigration(): void {
        if (!Request::verifyCsrf()) {
            Response::error('Invalid security token.');
        }

        $seedDemo = (bool)Request::post('seed_demo', true);
        $db = Database::getInstance();

        if (!$db) {
            // Attempt to connect and create the database if not exists
            try {
                $pdo = new PDO(
                    sprintf("mysql:host=%s;port=%s;charset=%s", DB_HOST, DB_PORT, DB_CHARSET),
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $db = Database::getInstance();
            } catch (Exception $e) {
                Response::error('Could not connect or create database: ' . $e->getMessage());
            }
        }

        try {
            // Execute schema.sql
            $schemaSql = file_get_contents(ROOT_PATH . '/database/schema.sql');
            $db->exec($schemaSql);

            // Seed demo data if requested
            if ($seedDemo) {
                $demoSql = file_get_contents(ROOT_PATH . '/database/demo_data.sql');
                $db->exec($demoSql);
            }

            Response::success('Database schema and initial configuration created successfully!');
        } catch (Exception $e) {
            Response::error('Migration error: ' . $e->getMessage());
        }
    }

    /**
     * Remove Demo Data safely
     */
    public function purgeDemoData(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid security token.');
        }

        $db = Database::getInstance();
        if (!$db) {
            Response::error('Database connection unavailable.');
        }

        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $tablesToTruncate = [
                'call_status_history', 'call_communications', 'service_report_photos',
                'service_report_items', 'service_reports', 'spare_request_items', 'spare_requests',
                'payments', 'invoice_items', 'invoices', 'purchase_items', 'purchases',
                'inventory_transactions', 'inventory', 'contract_machines', 'contracts',
                'pm_schedules', 'calls', 'machines', 'customer_contacts', 'customer_locations',
                'customers', 'audit_logs', 'notifications', 'customer_feedback'
            ];

            foreach ($tablesToTruncate as $t) {
                $db->exec("TRUNCATE TABLE `{$t}`;");
            }
            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            Response::success('Demo data purged successfully. Core system configuration and masters are preserved.');
        } catch (Exception $e) {
            Response::error('Error purging demo data: ' . $e->getMessage());
        }
    }
}
