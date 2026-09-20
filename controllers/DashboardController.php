<?php
/**
 * Main Operations Dashboard Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Call.php';
require_once ROOT_PATH . '/models/Customer.php';
require_once ROOT_PATH . '/models/Machine.php';
require_once ROOT_PATH . '/models/Contract.php';
require_once ROOT_PATH . '/models/Spare.php';
require_once ROOT_PATH . '/models/Inventory.php';

class DashboardController extends Controller {
    public function index(): void {
        $this->requireAuth();

        $role = Auth::role();
        if ($role === ROLE_ENGINEER) {
            Response::redirect('engineer');
        } elseif ($role === ROLE_CUSTOMER) {
            Response::redirect('customer');
        }

        $db = Database::getInstance();

        // 1. KPI Statistics
        $stats = [
            'open_calls'       => (int)($db->query("SELECT COUNT(*) FROM calls WHERE status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED')")->fetchColumn() ?: 0),
            'critical_calls'   => (int)($db->query("SELECT COUNT(*) FROM calls WHERE priority = 'CRITICAL' AND status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED')")->fetchColumn() ?: 0),
            'sla_breached'     => (int)($db->query("SELECT COUNT(*) FROM calls WHERE is_sla_breached = 1 OR (status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED') AND sla_resolution_deadline < NOW())")->fetchColumn() ?: 0),
            'active_contracts' => (int)($db->query("SELECT COUNT(*) FROM contracts WHERE status = 'ACTIVE'")->fetchColumn() ?: 0),
            'total_machines'   => (int)($db->query("SELECT COUNT(*) FROM machines WHERE deleted_at IS NULL")->fetchColumn() ?: 0),
            'pending_spares'   => (int)($db->query("SELECT COUNT(*) FROM spare_requests WHERE status = 'REQUESTED'")->fetchColumn() ?: 0),
            'low_stock_items'  => (int)($db->query("SELECT COUNT(*) FROM spare_items si JOIN inventory inv ON inv.spare_item_id = si.id WHERE inv.quantity <= si.min_stock_level")->fetchColumn() ?: 0),
            'pm_due'           => (int)($db->query("SELECT COUNT(*) FROM pm_schedules WHERE status = 'PENDING' AND schedule_date <= CURDATE()")->fetchColumn() ?: 0)
        ];

        // 2. Urgent / Active Live Call Queue
        $callModel = new Call();
        $liveCalls = $callModel->getDetailedList(['status' => '']);
        $recentLiveCalls = array_slice($liveCalls, 0, 8);

        // 3. Engineer Workload & Status Matrix
        $userModel = new User();
        $engineers = $userModel->getEngineers();

        // 4. Low Stock Alerts
        $lowStockStmt = $db->query("
            SELECT si.*, inv.quantity as current_stock, sc.name as category_name
            FROM spare_items si
            JOIN inventory inv ON inv.spare_item_id = si.id
            JOIN spare_categories sc ON si.category_id = sc.id
            WHERE inv.quantity <= si.min_stock_level
            LIMIT 5
        ");
        $lowStockList = $lowStockStmt->fetchAll();

        // 5. Expiring Contracts (Next 30 Days)
        $contractModel = new Contract();
        $expiringContracts = $contractModel->getExpiringContracts(30);

        $this->render('dashboard/index', [
            'pageTitle'         => 'Operations Dashboard',
            'activeMenu'        => 'dashboard',
            'stats'             => $stats,
            'recentLiveCalls'   => $recentLiveCalls,
            'engineers'         => $engineers,
            'lowStockList'      => $lowStockList,
            'expiringContracts' => $expiringContracts
        ], 'admin_layout');
    }
}
