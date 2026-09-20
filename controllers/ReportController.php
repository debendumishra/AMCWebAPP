<?php
/**
 * Reports & Business Intelligence Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

class ReportController extends Controller {
    public function index(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $db = Database::getInstance();

        // 1. Call Distribution by Status
        $statusDist = $db->query("
            SELECT status, COUNT(*) as count 
            FROM calls 
            GROUP BY status
        ")->fetchAll();

        // 2. Call Distribution by Priority
        $priorityDist = $db->query("
            SELECT priority, COUNT(*) as count 
            FROM calls 
            GROUP BY priority
        ")->fetchAll();

        // 3. Engineer Performance Metrics
        $engMetrics = $db->query("
            SELECT u.name as engineer_name, u.mobile,
                   COUNT(c.id) as total_assigned,
                   SUM(CASE WHEN c.status IN ('RESOLVED', 'CLOSED') THEN 1 ELSE 0 END) as completed_calls,
                   SUM(CASE WHEN c.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED') THEN 1 ELSE 0 END) as pending_calls,
                   SUM(CASE WHEN c.is_sla_breached = 1 THEN 1 ELSE 0 END) as sla_breaches
            FROM users u
            LEFT JOIN calls c ON c.assigned_engineer_id = u.id
            WHERE u.role_id = 5 AND u.deleted_at IS NULL
            GROUP BY u.id
        ")->fetchAll();

        // 4. Inventory Valuation Summary
        $stockSummary = $db->query("
            SELECT COUNT(*) as total_items,
                   SUM(inv.quantity) as total_quantity,
                   SUM(inv.quantity * si.purchase_price) as total_valuation
            FROM spare_items si
            JOIN inventory inv ON inv.spare_item_id = si.id
        ")->fetch();

        $this->render('reports/index', [
            'pageTitle'    => 'Enterprise Analytics & Reports',
            'activeMenu'   => 'reports',
            'statusDist'   => $statusDist,
            'priorityDist' => $priorityDist,
            'engMetrics'   => $engMetrics,
            'stockSummary' => $stockSummary
        ], 'admin_layout');
    }

    public function export(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        $type = Request::get('type', 'calls');

        $db = Database::getInstance();

        if ($type === 'calls') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=calls_export_' . date('Ymd_His') . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Call Number', 'Type', 'Priority', 'Customer', 'Machine Serial', 'Status', 'Logged At']);

            $calls = $db->query("
                SELECT c.call_number, c.call_type, c.priority, cust.company_name, m.serial_number, c.status, c.created_at
                FROM calls c
                JOIN customers cust ON c.customer_id = cust.id
                LEFT JOIN machines m ON c.machine_id = m.id
                ORDER BY c.id DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($calls as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        }
    }
}
