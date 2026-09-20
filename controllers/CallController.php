<?php
/**
 * Service Call & Fast Call-Center Management Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Call.php';
require_once ROOT_PATH . '/models/Customer.php';
require_once ROOT_PATH . '/models/Machine.php';
require_once ROOT_PATH . '/models/User.php';

class CallController extends Controller {
    private Call $callModel;
    private Customer $customerModel;
    private Machine $machineModel;
    private User $userModel;

    public function __construct() {
        $this->callModel = new Call();
        $this->customerModel = new Customer();
        $this->machineModel = new Machine();
        $this->userModel = new User();
    }

    public function index(): void {
        $this->requireAuth();
        $statusFilter = Request::get('status', '');
        $priorityFilter = Request::get('priority', '');

        $calls = $this->callModel->getDetailedList([
            'status'   => $statusFilter,
            'priority' => $priorityFilter
        ]);

        $engineers = $this->userModel->getEngineers();

        $this->render('calls/index', [
            'pageTitle'  => 'Service Calls & Dispatch Queue',
            'activeMenu' => 'calls',
            'calls'      => $calls,
            'engineers'  => $engineers,
            'status'     => $statusFilter,
            'priority'   => $priorityFilter
        ], 'admin_layout');
    }

    public function create(): void {
        $this->requireAuth();
        $db = Database::getInstance();

        $customerId = Request::get('customer_id');
        $machineId = Request::get('machine_id');

        $customers = $this->customerModel->all([], 'company_name ASC');
        $categories = $db->query("SELECT * FROM problem_categories WHERE is_active = 1")->fetchAll();
        $engineers = $this->userModel->getEngineers();

        $selectedMachine = null;
        $customerLocations = [];
        $customerMachines = [];

        if ($machineId) {
            $selectedMachine = $this->machineModel->find((int)$machineId);
            if ($selectedMachine && empty($customerId)) {
                $customerId = $selectedMachine['customer_id'];
            }
        }

        if ($customerId) {
            $locModel = new CustomerLocation();
            $customerLocations = $locModel->getByCustomerId((int)$customerId);
        }
        $customerMachines = $this->machineModel->getDetailedList($customerId ? ['customer_id' => (int)$customerId] : []);

        $this->render('calls/create', [
            'pageTitle'         => 'Log Service Ticket',
            'activeMenu'        => 'calls',
            'customers'         => $customers,
            'categories'        => $categories,
            'engineers'         => $engineers,
            'selectedCustomerId'=> $customerId,
            'selectedMachineId' => $machineId,
            'selectedMachine'   => $selectedMachine,
            'customerLocations' => $customerLocations,
            'customerMachines'  => $customerMachines
        ], 'admin_layout');
    }

    public function store(): void {
        $this->requireAuth();
        if (!Request::verifyCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            Response::redirect('calls/create');
        }

        $callNo = NumberGenerator::generate('call');
        $callType = Request::post('call_type', 'AMC');
        $priority = Request::post('priority', 'MEDIUM');
        $customerId = (int)Request::post('customer_id');
        $locationId = (int)Request::post('location_id');
        $machineId = Request::post('machine_id') ? (int)Request::post('machine_id') : null;
        $reportedIssue = Request::post('reported_issue');
        $callerName = Request::post('caller_name');
        $callerMobile = Request::post('caller_mobile');
        $assignedEngineerId = Request::post('assigned_engineer_id') ? (int)Request::post('assigned_engineer_id') : null;

        // Handle Unregistered Caller
        if (empty($customerId)) {
            $unregisteredName = Request::post('unregistered_company');
            $unregisteredAddress = Request::post('unregistered_address');

            if (empty($unregisteredName) || empty($callerMobile)) {
                $this->setFlash('error', 'Caller name, company, and mobile number are mandatory.');
                Response::redirect('calls/create');
            }

            // Create new customer record automatically
            $custCode = NumberGenerator::generate('customer');
            $customerId = $this->customerModel->create([
                'customer_code'   => $custCode,
                'company_name'    => $unregisteredName,
                'contact_person'  => $callerName,
                'mobile'          => $callerMobile,
                'email'           => Request::post('caller_email', 'caller@temp.com'),
                'billing_address' => $unregisteredAddress,
                'city'            => 'Mumbai',
                'state'           => 'Maharashtra',
                'pincode'         => '400001',
                'customer_type'   => 'PAID',
                'status'          => 'ACTIVE'
            ]);

            $locModel = new CustomerLocation();
            $locationId = $locModel->create([
                'customer_id'   => $customerId,
                'location_name' => 'Main Site',
                'address'       => $unregisteredAddress,
                'city'          => 'Mumbai',
                'state'         => 'Maharashtra',
                'pincode'       => '400001',
                'contact_person'=> $callerName,
                'mobile'        => $callerMobile
            ]);
            $callType = 'PAID';
        }

        // Calculate SLA Response & Resolution Deadlines based on SLA Master
        $db = Database::getInstance();
        $slaStmt = $db->prepare("SELECT * FROM sla_master WHERE priority = :pri LIMIT 1");
        $slaStmt->execute(['pri' => $priority]);
        $sla = $slaStmt->fetch();

        $respMins = $sla ? (int)$sla['response_time_minutes'] : 120;
        $resMins = $sla ? (int)$sla['resolution_time_minutes'] : 480;

        $respDeadline = date('Y-m-d H:i:s', strtotime("+{$respMins} minutes"));
        $resDeadline = date('Y-m-d H:i:s', strtotime("+{$resMins} minutes"));

        // Check if machine is under active contract
        $contractId = null;
        if ($machineId) {
            $cmStmt = $db->prepare("SELECT contract_id FROM contract_machines WHERE machine_id = :mid AND status = 'ACTIVE' LIMIT 1");
            $cmStmt->execute(['mid' => $machineId]);
            $contractId = $cmStmt->fetchColumn() ?: null;
        }

        $initialStatus = $assignedEngineerId ? STATUS_ASSIGNED : STATUS_NEW;

        $callId = $this->callModel->create([
            'call_number'            => $callNo,
            'call_type'              => $callType,
            'priority'               => $priority,
            'customer_id'            => $customerId,
            'location_id'            => $locationId,
            'machine_id'             => $machineId,
            'contract_id'            => $contractId,
            'problem_category_id'    => Request::post('problem_category_id'),
            'problem_id'             => Request::post('problem_id'),
            'caller_name'            => $callerName,
            'caller_mobile'          => $callerMobile,
            'caller_email'           => Request::post('caller_email'),
            'reported_issue'         => $reportedIssue,
            'assigned_engineer_id'   => $assignedEngineerId,
            'status'                 => $initialStatus,
            'sla_response_deadline'  => $respDeadline,
            'sla_resolution_deadline'=> $resDeadline,
            'created_by'             => Auth::id()
        ]);

        // Record Initial Timeline History
        $db->prepare("
            INSERT INTO call_status_history (call_id, old_status, new_status, changed_by, remarks, created_at)
            VALUES (:cid, NULL, :st, :uid, 'Ticket created via Service Desk', NOW())
        ")->execute([
            'cid' => $callId,
            'st'  => $initialStatus,
            'uid' => Auth::id()
        ]);

        $this->setFlash('success', "Service call {$callNo} logged successfully.");
        Response::redirect('calls/' . $callId);
    }

    public function show(int $id): void {
        $this->requireAuth();
        $call = $this->callModel->getDetailed($id);
        if (!$call) {
            Response::redirect('calls');
        }

        $timeline = $this->callModel->getTimeline($id);
        $engineers = $this->userModel->getEngineers();

        // Fetch communications
        $db = Database::getInstance();
        $msgStmt = $db->prepare("
            SELECT cc.*, u.name as sender_name, r.name as role_name
            FROM call_communications cc
            JOIN users u ON cc.sender_id = u.id
            JOIN roles r ON u.role_id = r.id
            WHERE cc.call_id = :cid
            ORDER BY cc.id ASC
        ");
        $msgStmt->execute(['cid' => $id]);
        $messages = $msgStmt->fetchAll();

        // Fetch spare requests if any
        $srStmt = $db->prepare("SELECT * FROM spare_requests WHERE call_id = :cid ORDER BY id DESC");
        $srStmt->execute(['cid' => $id]);
        $spareRequests = $srStmt->fetchAll();

        // Fetch Service Report if exists
        $repStmt = $db->prepare("SELECT * FROM service_reports WHERE call_id = :cid LIMIT 1");
        $repStmt->execute(['cid' => $id]);
        $serviceReport = $repStmt->fetch();

        $this->render('calls/show', [
            'pageTitle'     => 'Call: ' . $call['call_number'],
            'activeMenu'    => 'calls',
            'call'          => $call,
            'timeline'      => $timeline,
            'messages'      => $messages,
            'engineers'     => $engineers,
            'spareRequests' => $spareRequests,
            'serviceReport' => $serviceReport
        ], 'admin_layout');
    }

    public function updateStatus(int $id): void {
        $this->requireAuth();
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $newStatus = Request::post('status');
        $remarks = Request::post('remarks');
        $lat = Request::post('latitude');
        $lng = Request::post('longitude');

        $this->callModel->transitionStatus(
            $id,
            $newStatus,
            $remarks,
            !empty($lat) ? (float)$lat : null,
            !empty($lng) ? (float)$lng : null
        );

        $this->setFlash('success', "Ticket status changed to {$newStatus}.");
        Response::redirect('calls/' . $id);
    }

    public function assignEngineer(int $id): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $engineerId = (int)Request::post('engineer_id');
        $engineer = $this->userModel->find($engineerId);

        if (!$engineer) {
            $this->setFlash('error', 'Technician not found.');
            Response::redirect('calls/' . $id);
        }

        $this->callModel->update($id, [
            'assigned_engineer_id' => $engineerId,
            'status'               => STATUS_ASSIGNED
        ]);

        $this->callModel->transitionStatus($id, STATUS_ASSIGNED, "Assigned to {$engineer['name']}");

        $this->setFlash('success', "Ticket assigned to {$engineer['name']}.");
        Response::redirect('calls/' . $id);
    }

    public function addMessage(int $id): void {
        $this->requireAuth();
        if (!Request::verifyCsrf()) {
            Response::error('Invalid security token.');
        }

        $msg = Request::post('message');
        $isInternal = Request::post('is_internal', 0);

        if (!empty($msg)) {
            $db = Database::getInstance();
            $db->prepare("
                INSERT INTO call_communications (call_id, sender_id, message, is_internal_only, created_at)
                VALUES (:cid, :uid, :msg, :int, NOW())
            ")->execute([
                'cid' => $id,
                'uid' => Auth::id(),
                'msg' => $msg,
                'int' => $isInternal ? 1 : 0
            ]);
        }

        $this->setFlash('success', 'Message posted to timeline.');
        Response::redirect('calls/' . $id);
    }

    /**
     * Print Official Digital Job Card / Service Report
     */
    public function printJobCard(int $id): void {
        $this->requireAuth();
        $call = $this->callModel->getDetailed($id);
        if (!$call) {
            die("Service call not found.");
        }

        $db = Database::getInstance();
        $repStmt = $db->prepare("
            SELECT sr.*, u.name as engineer_name, u.mobile as engineer_mobile, u.employee_code as engineer_code
            FROM service_reports sr
            JOIN users u ON sr.engineer_id = u.id
            WHERE sr.call_id = :cid
            ORDER BY sr.id DESC
            LIMIT 1
        ");
        $repStmt->execute(['cid' => $id]);
        $serviceReport = $repStmt->fetch();

        // Fetch replaced/issued spares
        $spareStmt = $db->prepare("
            SELECT sri.*, si.name as spare_name, si.sku, si.capacity_spec, si.unit, si.hsn_code,
                   sr.request_number, sr.status as req_status
            FROM spare_request_items sri
            JOIN spare_requests sr ON sri.spare_request_id = sr.id
            JOIN spare_items si ON sri.spare_item_id = si.id
            WHERE sr.call_id = :cid
        ");
        $spareStmt->execute(['cid' => $id]);
        $spares = $spareStmt->fetchAll();

        // System settings for company details
        $settingsStmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $this->render('calls/print_jobcard', [
            'pageTitle'     => 'Service Report - ' . ($serviceReport['report_number'] ?? $call['call_number']),
            'call'          => $call,
            'serviceReport' => $serviceReport,
            'spares'        => $spares,
            'settings'      => $settings
        ], 'none');
    }
}
