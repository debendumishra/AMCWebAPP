<?php
/**
 * Preventive Maintenance (PM) Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/PM.php';
require_once ROOT_PATH . '/models/Call.php';
require_once ROOT_PATH . '/models/Contract.php';
require_once ROOT_PATH . '/models/Customer.php';

class PMController extends Controller {
    private PM $pmModel;

    public function __construct() {
        $this->pmModel = new PM();
    }

    public function index(): void {
        $this->requireAuth();
        
        $status = Request::get('status');
        $customerId = Request::get('customer_id');
        $contractId = Request::get('contract_id');

        $filters = [];
        if (!empty($status)) $filters['status'] = $status;
        if (!empty($customerId)) $filters['customer_id'] = (int)$customerId;
        if (!empty($contractId)) $filters['contract_id'] = (int)$contractId;

        $schedules = $this->pmModel->getDetailedList($filters);
        $dueSchedules = $this->pmModel->getDueSchedules();
        $dueCount = count($dueSchedules);

        $db = Database::getInstance();
        $totalCount = (int)$db->query("SELECT COUNT(*) FROM pm_schedules")->fetchColumn();
        $pendingCount = (int)$db->query("SELECT COUNT(*) FROM pm_schedules WHERE status = 'PENDING'")->fetchColumn();
        $generatedCount = (int)$db->query("SELECT COUNT(*) FROM pm_schedules WHERE status = 'GENERATED'")->fetchColumn();
        $completedCount = (int)$db->query("SELECT COUNT(*) FROM pm_schedules WHERE status = 'COMPLETED'")->fetchColumn();

        $customers = (new Customer())->all([], 'company_name ASC');
        $contracts = (new Contract())->all(['status' => 'ACTIVE'], 'contract_number ASC');

        $this->render('pm/index', [
            'pageTitle'      => 'Preventive Maintenance (PM) Schedules',
            'activeMenu'     => 'pm',
            'schedules'      => $schedules,
            'dueCount'       => $dueCount,
            'totalCount'     => $totalCount,
            'pendingCount'   => $pendingCount,
            'generatedCount' => $generatedCount,
            'completedCount' => $completedCount,
            'customers'      => $customers,
            'contracts'      => $contracts,
            'currentStatus'  => $status,
            'currentCustId'  => $customerId
        ], 'admin_layout');
    }

    public function syncSchedules(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $created = $this->pmModel->syncAllContractSchedules();
        if ($created > 0) {
            $this->setFlash('success', "Synced PM schedule planner: Created {$created} new recurring PM visit schedule(s) for active contracts.");
        } else {
            $this->setFlash('info', 'PM schedules are already up-to-date for all active contracts and mapped assets.');
        }

        Response::redirect('pm');
    }

    public function generateSingleCall(int $id): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $callId = $this->pmModel->generateSingleCall($id, Auth::id());
        if ($callId > 0) {
            $this->setFlash('success', "PM Service Ticket generated successfully in dispatch queue.");
        } else {
            $this->setFlash('error', "Could not generate PM ticket. Please verify schedule details.");
        }

        Response::redirect('pm');
    }

    public function generateDueCalls(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $dueSchedules = $this->pmModel->getDueSchedules();
        if (empty($dueSchedules)) {
            $this->setFlash('info', 'No PM schedules are due for ticket generation at this time.');
            Response::redirect('pm');
            return;
        }

        $generatedCount = 0;
        foreach ($dueSchedules as $pm) {
            $callId = $this->pmModel->generateSingleCall((int)$pm['id'], Auth::id());
            if ($callId > 0) {
                $generatedCount++;
            }
        }

        $this->setFlash('success', "Generated {$generatedCount} PM service call(s) into active dispatch queue.");
        Response::redirect('pm');
    }

    public function checklists(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
        $db = Database::getInstance();
        $checklists = $db->query("
            SELECT pc.*, at.name as asset_type_name,
                   (SELECT COUNT(*) FROM pm_checklist_items pci WHERE pci.checklist_id = pc.id) as items_count
            FROM pm_checklists pc
            JOIN asset_types at ON pc.asset_type_id = at.id
            ORDER BY pc.id ASC
        ")->fetchAll();

        $assetTypes = $db->query("SELECT * FROM asset_types WHERE is_active = 1")->fetchAll();

        $this->render('pm/checklists', [
            'pageTitle'  => 'PM Asset Checklists Master',
            'activeMenu' => 'pm',
            'checklists' => $checklists,
            'assetTypes' => $assetTypes
        ], 'admin_layout');
    }

    public function storeChecklist(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $title = Request::post('title');
        $assetTypeId = (int)Request::post('asset_type_id');
        $tasks = Request::post('tasks'); // Multiple tasks or single

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO pm_checklists (asset_type_id, title, is_active) VALUES (:aid, :title, 1)");
        $stmt->execute(['aid' => $assetTypeId, 'title' => $title]);
        $clId = (int)$db->lastInsertId();

        if (is_array($tasks)) {
            $sort = 1;
            foreach ($tasks as $task) {
                $t = trim($task);
                if (!empty($t)) {
                    $db->prepare("INSERT INTO pm_checklist_items (checklist_id, task_description, sort_order, is_mandatory) VALUES (:cid, :task, :sort, 1)")
                       ->execute(['cid' => $clId, 'task' => $t, 'sort' => $sort++]);
                }
            }
        } elseif (!empty(Request::post('task_description'))) {
            $db->prepare("INSERT INTO pm_checklist_items (checklist_id, task_description, sort_order, is_mandatory) VALUES (:cid, :task, 1, 1)")
               ->execute(['cid' => $clId, 'task' => trim(Request::post('task_description'))]);
        }

        $this->setFlash('success', "PM Checklist created successfully.");
        Response::redirect('pm/checklists');
    }
}
