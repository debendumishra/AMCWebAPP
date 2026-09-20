<?php
/**
 * System Settings, Master Tables & Audit Logs Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Setting.php';

class SettingsController extends Controller {
    public function index(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        $db = Database::getInstance();
        $settingsRows = $db->query("SELECT * FROM system_settings ORDER BY setting_group ASC, setting_key ASC")->fetchAll();

        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $this->render('settings/index', [
            'pageTitle'  => 'System Settings & Parameters',
            'activeMenu' => 'settings',
            'settings'   => $settings
        ], 'admin_layout');
    }

    public function update(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $all = Request::all();
        unset($all['csrf_token']);

        foreach ($all as $key => $val) {
            Setting::set($key, (string)$val);
        }

        $this->setFlash('success', 'System parameters and company settings updated successfully.');
        Response::redirect('settings');
    }

    public function masters(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        $db = Database::getInstance();

        $assetTypes = $db->query("SELECT * FROM asset_types ORDER BY id ASC")->fetchAll();
        $problemCats = $db->query("SELECT * FROM problem_categories ORDER BY id ASC")->fetchAll();
        $contractTypes = $db->query("SELECT * FROM contract_types ORDER BY id ASC")->fetchAll();
        $spareCategories = $db->query("SELECT * FROM spare_categories ORDER BY id ASC")->fetchAll();
        $spareBrands = $db->query("SELECT * FROM spare_brands ORDER BY id ASC")->fetchAll();

        $this->render('settings/masters', [
            'pageTitle'       => 'Configurable Master Tables',
            'activeMenu'      => 'masters',
            'assetTypes'      => $assetTypes,
            'problemCats'     => $problemCats,
            'contractTypes'   => $contractTypes,
            'spareCategories' => $spareCategories,
            'spareBrands'     => $spareBrands
        ], 'admin_layout');
    }

    public function saveMaster(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $type = Request::post('master_type');
        $name = Request::post('name');
        $db = Database::getInstance();

        if ($type === 'asset_type') {
            $category = Request::post('category', 'HARDWARE');
            $icon = Request::post('icon', 'bi-pc-display');
            $db->prepare("INSERT INTO asset_types (name, category, icon, is_active) VALUES (:name, :cat, :icon, 1)")
               ->execute(['name' => $name, 'cat' => $category, 'icon' => $icon]);
        } elseif ($type === 'problem_category') {
            $db->prepare("INSERT INTO problem_categories (name, is_active) VALUES (:name, 1)")
               ->execute(['name' => $name]);
        } elseif ($type === 'spare_brand') {
            $db->prepare("INSERT INTO spare_brands (name, is_active) VALUES (:name, 1)")
               ->execute(['name' => $name]);
        }

        $this->setFlash('success', "Master record '{$name}' added successfully.");
        Response::redirect('settings/masters');
    }

    public function auditLogs(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        $db = Database::getInstance();
        $logs = $db->query("
            SELECT al.*, u.name as user_name, r.name as role_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY al.id DESC
            LIMIT 100
        ")->fetchAll();

        $this->render('settings/audit', [
            'pageTitle'  => 'System Audit Trail Log',
            'activeMenu' => 'audit',
            'logs'       => $logs
        ], 'admin_layout');
    }
}
