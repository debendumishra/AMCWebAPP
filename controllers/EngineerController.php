<?php
/**
 * Field Engineer Mobile Workflow Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Call.php';
require_once ROOT_PATH . '/models/Customer.php';
require_once ROOT_PATH . '/models/Machine.php';
require_once ROOT_PATH . '/models/Spare.php';
require_once ROOT_PATH . '/models/Inventory.php';
require_once ROOT_PATH . '/core/NumberGenerator.php';

class EngineerController extends Controller {
    private Call $callModel;
    private Customer $customerModel;
    private Machine $machineModel;
    private Spare $spareModel;

    public function __construct() {
        $this->callModel = new Call();
        $this->customerModel = new Customer();
        $this->machineModel = new Machine();
        $this->spareModel = new Spare();
    }

    public function createCall(): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);

        $customerId = Request::get('customer_id');
        $machineId = Request::get('machine_id');

        $customers = $this->customerModel->all([], 'company_name ASC');

        $customerLocations = [];
        $selectedMachine = null;

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

        $machines = $this->machineModel->getDetailedList($customerId ? ['customer_id' => (int)$customerId] : []);

        $this->render('engineer/create_call', [
            'pageTitle'          => 'Raise Service Complaint',
            'activeMenu'         => 'create_call',
            'customers'          => $customers,
            'selectedCustomerId' => $customerId ? (int)$customerId : null,
            'selectedMachineId'  => $machineId ? (int)$machineId : null,
            'selectedMachine'    => $selectedMachine,
            'customerLocations'  => $customerLocations,
            'machines'           => $machines
        ], 'engineer_layout');
    }

    public function storeCall(): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        if (!Request::verifyCsrf()) {
            $this->setFlash('error', 'Security token expired. Please try again.');
            Response::redirect('engineer/calls/create');
        }

        $customerId = (int)Request::post('customer_id');
        $locationId = (int)Request::post('location_id');
        $machineId = Request::post('machine_id') ? (int)Request::post('machine_id') : null;
        $callType = Request::post('call_type', 'BREAKDOWN');
        $priority = Request::post('priority', 'MEDIUM');
        $reportedIssue = trim(Request::post('reported_issue', ''));
        $callerName = trim(Request::post('caller_name', ''));
        $callerMobile = trim(Request::post('caller_mobile', ''));
        $assignMode = Request::post('assign_mode', 'self_onsite'); // 'self_onsite', 'self_assigned', 'queue'

        if (empty($customerId)) {
            $this->setFlash('error', 'Please select a customer.');
            Response::redirect('engineer/calls/create');
        }

        if (empty($reportedIssue)) {
            $this->setFlash('error', 'Please provide problem / defect description.');
            Response::redirect('engineer/calls/create');
        }

        // Auto resolve location if not set
        if (empty($locationId) && !empty($machineId)) {
            $mach = $this->machineModel->find($machineId);
            if ($mach && !empty($mach['location_id'])) {
                $locationId = (int)$mach['location_id'];
            }
        }

        if (empty($locationId)) {
            $locModel = new CustomerLocation();
            $locs = $locModel->getByCustomerId($customerId);
            if (!empty($locs)) {
                $locationId = (int)$locs[0]['id'];
            }
        }

        $callNo = NumberGenerator::generate('call');
        $engineerId = Auth::id();
        $user = Auth::user();
        $db = Database::getInstance();

        // Calculate SLA deadlines from sla_master
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

        $status = STATUS_NEW;
        $assignedEngId = null;
        $responseAt = null;

        if ($assignMode === 'self_onsite') {
            $status = STATUS_DIAGNOSIS;
            $assignedEngId = $engineerId;
            $responseAt = date('Y-m-d H:i:s');
        } elseif ($assignMode === 'self_assigned') {
            $status = STATUS_ASSIGNED;
            $assignedEngId = $engineerId;
        } else {
            $status = STATUS_NEW;
            $assignedEngId = null;
        }

        $callId = $this->callModel->create([
            'call_number'            => $callNo,
            'customer_id'            => $customerId,
            'location_id'            => $locationId,
            'machine_id'             => $machineId,
            'contract_id'            => $contractId,
            'call_type'              => $callType,
            'priority'               => $priority,
            'reported_issue'         => $reportedIssue,
            'caller_name'            => $callerName ?: ($user['name'] ?? 'Field Engineer'),
            'caller_mobile'          => $callerMobile ?: ($user['mobile'] ?? ''),
            'assigned_engineer_id'   => $assignedEngId,
            'response_at'            => $responseAt,
            'status'                 => $status,
            'sla_response_deadline'  => $respDeadline,
            'sla_resolution_deadline'=> $resDeadline,
            'created_by'             => $engineerId
        ]);

        $logStmt = $db->prepare("
            INSERT INTO call_status_history (call_id, old_status, new_status, changed_by, remarks, ip_address, created_at)
            VALUES (:call_id, NULL, :new_status, :changed_by, :remarks, :ip, NOW())
        ");
        $remarks = "Ticket logged directly by Field Engineer " . ($user['name'] ?? '');
        if ($assignMode === 'self_onsite') {
            $remarks .= " (Direct on-site diagnosis)";
        }
        $logStmt->execute([
            'call_id'    => $callId,
            'new_status' => $status,
            'changed_by' => $engineerId,
            'remarks'    => $remarks,
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        $this->setFlash('success', "Service Call {$callNo} logged successfully.");

        if ($assignedEngId) {
            Response::redirect("engineer/calls/{$callId}");
        } else {
            Response::redirect('engineer/calls');
        }
    }

    public function index(): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        $engineerId = Auth::id();

        // Get calls assigned to this engineer
        $todayCalls = $this->callModel->getDetailedList(['engineer_id' => $engineerId]);

        $activeCalls = array_filter($todayCalls, fn($c) => !in_array($c['status'], ['RESOLVED', 'CLOSED', 'CANCELLED']));
        $completedCalls = array_filter($todayCalls, fn($c) => in_array($c['status'], ['RESOLVED', 'CLOSED']));

        $this->render('engineer/index', [
            'pageTitle'      => "Today's Schedule",
            'activeMenu'     => 'dashboard',
            'activeCalls'    => array_values($activeCalls),
            'completedCalls' => array_values($completedCalls)
        ], 'engineer_layout');
    }

    public function calls(): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        $engineerId = Auth::id();
        $allCalls = $this->callModel->getDetailedList(['engineer_id' => $engineerId]);

        $this->render('engineer/calls', [
            'pageTitle'  => 'All Assigned Tasks',
            'activeMenu' => 'calls',
            'calls'      => $allCalls
        ], 'engineer_layout');
    }

    public function callDetail(int $id): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        $call = $this->callModel->getDetailed($id);

        if (!$call) {
            Response::redirect('engineer');
        }

        $spares = $this->spareModel->all(['status' => 'ACTIVE'], 'name ASC');

        // Check if draft or submitted job card exists
        $db = Database::getInstance();
        $repStmt = $db->prepare("SELECT * FROM service_reports WHERE call_id = :cid LIMIT 1");
        $repStmt->execute(['cid' => $id]);
        $serviceReport = $repStmt->fetch();

        // Fetch requested/issued spares for this call
        $spareStmt = $db->prepare("
            SELECT sri.*, si.name as spare_name, si.sku, si.capacity_spec, sr.request_number, sr.status as req_status
            FROM spare_request_items sri
            JOIN spare_requests sr ON sri.spare_request_id = sr.id
            JOIN spare_items si ON sri.spare_item_id = si.id
            WHERE sr.call_id = :cid
        ");
        $spareStmt->execute(['cid' => $id]);
        $callSpares = $spareStmt->fetchAll();

        $this->render('engineer/call_detail', [
            'pageTitle'     => 'Task: ' . $call['call_number'],
            'activeMenu'    => 'calls',
            'call'          => $call,
            'spares'        => $spares,
            'callSpares'    => $callSpares,
            'serviceReport' => $serviceReport
        ], 'engineer_layout');
    }

    public function updateJourney(int $id): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $action = Request::post('action'); // 'accept', 'start_journey', 'arrive', 'start_work'
        $lat = Request::post('latitude');
        $lng = Request::post('longitude');
        $accuracy = Request::post('accuracy');

        $statusMap = [
            'accept'        => STATUS_ENGINEER_ACCEPTED,
            'start_journey' => STATUS_ON_THE_WAY,
            'arrive'        => STATUS_ARRIVED,
            'start_work'    => STATUS_DIAGNOSIS
        ];

        $newStatus = $statusMap[$action] ?? STATUS_ARRIVED;
        $remarks = "Technician action: " . strtoupper(str_replace('_', ' ', $action));

        $this->callModel->transitionStatus($id, $newStatus, $remarks, !empty($lat) ? (float)$lat : null, !empty($lng) ? (float)$lng : null);

        // Record GPS event in engineer_locations table
        if (!empty($lat) && !empty($lng)) {
            $db = Database::getInstance();
            $db->prepare("
                INSERT INTO engineer_locations (engineer_id, call_id, event_type, latitude, longitude, accuracy, timestamp)
                VALUES (:eid, :cid, :evt, :lat, :lng, :acc, NOW())
            ")->execute([
                'eid' => Auth::id(),
                'cid' => $id,
                'evt' => $action,
                'lat' => (float)$lat,
                'lng' => (float)$lng,
                'acc' => !empty($accuracy) ? (float)$accuracy : 10.0
            ]);
        }

        $this->setFlash('success', "Status updated to " . str_replace('_', ' ', $newStatus));
        Response::redirect('engineer/calls/' . $id);
    }

    public function requestSpare(int $id): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        if (!Request::verifyCsrf()) {
            $this->setFlash('error', 'Invalid security token. Please try again.');
            Response::redirect('engineer/calls/' . $id);
            return;
        }

        $call = $this->callModel->getDetailed($id);
        if (!$call) {
            $this->setFlash('error', 'Service call not found.');
            Response::redirect('engineer');
            return;
        }

        $spareItemId = (int)Request::post('spare_item_id');
        $qty = max(1, (int)Request::post('quantity', 1));
        $remarks = trim((string)Request::post('remarks', ''));

        if ($spareItemId <= 0) {
            $this->setFlash('error', 'Please select a valid spare part from the dropdown.');
            Response::redirect('engineer/calls/' . $id);
            return;
        }

        $spare = $this->spareModel->find($spareItemId);
        if (!$spare) {
            $this->setFlash('error', 'Selected spare part was not found in the catalog.');
            Response::redirect('engineer/calls/' . $id);
            return;
        }

        $reqNo = NumberGenerator::generate('spare_req');
        $db = Database::getInstance();

        try {
            if (!$db->inTransaction()) {
                $db->beginTransaction();
            }

            $stmt = $db->prepare("
                INSERT INTO spare_requests (request_number, call_id, engineer_id, machine_id, status, approval_remarks, created_at)
                VALUES (:req_no, :cid, :eid, :mid, 'REQUESTED', :rem, NOW())
            ");
            $stmt->execute([
                'req_no' => $reqNo,
                'cid'    => $id,
                'eid'    => Auth::id() ?: (int)($call['assigned_engineer_id'] ?? 1),
                'mid'    => !empty($call['machine_id']) ? (int)$call['machine_id'] : null,
                'rem'    => !empty($remarks) ? $remarks : null
            ]);
            $reqId = (int)$db->lastInsertId();

            // Insert Request Item
            $isChargeable = empty($call['is_spares_covered']) ? 1 : 0;

            $iStmt = $db->prepare("
                INSERT INTO spare_request_items (spare_request_id, spare_item_id, requested_qty, is_chargeable, unit_price, status)
                VALUES (:rid, :sid, :qty, :chg, :price, 'REQUESTED')
            ");
            $iStmt->execute([
                'rid'   => $reqId,
                'sid'   => $spareItemId,
                'qty'   => $qty,
                'chg'   => $isChargeable,
                'price' => (float)($spare['selling_price'] ?? 0.0)
            ]);

            // Update call status to SPARE_REQUIRED
            $this->callModel->transitionStatus($id, STATUS_SPARE_REQUIRED, "Spare part requested ({$spare['name']}) - Req #{$reqNo}");

            if ($db->inTransaction()) {
                $db->commit();
            }
            $this->setFlash('success', "Spare requisition {$reqNo} for '{$spare['name']}' submitted successfully for approval!");
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->setFlash('error', "Error submitting spare requisition: " . $e->getMessage());
        }

        Response::redirect('engineer/calls/' . $id);
    }

    public function jobCard(int $id): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        $call = $this->callModel->getDetailed($id);

        if (!$call) {
            Response::redirect('engineer');
        }

        $db = Database::getInstance();
        $repStmt = $db->prepare("SELECT * FROM service_reports WHERE call_id = :cid LIMIT 1");
        $repStmt->execute(['cid' => $id]);
        $serviceReport = $repStmt->fetch();

        // Fetch requested/issued spares for this call
        $spareStmt = $db->prepare("
            SELECT sri.*, si.name as spare_name, si.sku, si.capacity_spec, si.unit,
                   sr.request_number, sr.status as req_status
            FROM spare_request_items sri
            JOIN spare_requests sr ON sri.spare_request_id = sr.id
            JOIN spare_items si ON sri.spare_item_id = si.id
            WHERE sr.call_id = :cid
        ");
        $spareStmt->execute(['cid' => $id]);
        $callSpares = $spareStmt->fetchAll();

        $this->render('engineer/jobcard', [
            'pageTitle'     => 'Digital Job Card: ' . $call['call_number'],
            'activeMenu'    => 'calls',
            'call'          => $call,
            'callSpares'    => $callSpares,
            'serviceReport' => $serviceReport
        ], 'engineer_layout');
    }

    public function submitJobCard(int $id): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $call = $this->callModel->getDetailed($id);
        $diagnosis = Request::post('diagnosis');
        $actionTaken = Request::post('action_taken');
        $signerName = Request::post('customer_signed_name');
        $signatureData = Request::post('customer_signature_data');
        $isDraft = (int)Request::post('is_draft', 0);
        $lat = Request::post('latitude');
        $lng = Request::post('longitude');

        if (empty($diagnosis) || empty($actionTaken)) {
            $this->setFlash('error', 'Diagnosis and Action Taken fields are required.');
            Response::redirect('engineer/calls/' . $id . '/jobcard');
        }

        if (!$isDraft && empty($signatureData)) {
            $this->setFlash('error', 'Customer signature is required before completing job card.');
            Response::redirect('engineer/calls/' . $id . '/jobcard');
        }

        $db = Database::getInstance();
        $reportNo = NumberGenerator::generate('service_report');

        // Upsert Service Report with signature GPS
        $stmt = $db->prepare("
            INSERT INTO service_reports 
            (report_number, call_id, engineer_id, customer_id, machine_id, diagnosis, action_taken, customer_signed_name, customer_signature_data, signature_latitude, signature_longitude, signed_at, is_draft, created_at)
            VALUES (:rno, :cid, :eid, :custid, :mid, :diag, :act, :sname, :sdata, :lat, :lng, NOW(), :draft, NOW())
            ON DUPLICATE KEY UPDATE 
            diagnosis = :diag2, action_taken = :act2, customer_signed_name = :sname2, customer_signature_data = :sdata2, signature_latitude = :lat2, signature_longitude = :lng2, is_draft = :draft2, updated_at = NOW()
        ");
        $stmt->execute([
            'rno'    => $reportNo,
            'cid'    => $id,
            'eid'    => Auth::id(),
            'custid' => $call['customer_id'],
            'mid'    => $call['machine_id'],
            'diag'   => $diagnosis,
            'act'    => $actionTaken,
            'sname'  => $signerName,
            'sdata'  => $signatureData,
            'lat'    => !empty($lat) ? (float)$lat : null,
            'lng'    => !empty($lng) ? (float)$lng : null,
            'draft'  => $isDraft,
            'diag2'  => $diagnosis,
            'act2'   => $actionTaken,
            'sname2' => $signerName,
            'sdata2' => $signatureData,
            'lat2'   => !empty($lat) ? (float)$lat : null,
            'lng2'   => !empty($lng) ? (float)$lng : null,
            'draft2' => $isDraft
        ]);

        if (!$isDraft) {
            $this->callModel->transitionStatus($id, STATUS_RESOLVED, "Job Card signed by {$signerName}. Work completed.", !empty($lat) ? (float)$lat : null, !empty($lng) ? (float)$lng : null);
            $this->setFlash('success', "Service call resolved! Job card {$reportNo} submitted with e-signature.");
            Response::redirect('engineer');
        } else {
            $this->setFlash('success', "Job card draft saved successfully.");
            Response::redirect('engineer/calls/' . $id . '/jobcard');
        }
    }

    public function spares(): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        $invModel = new Inventory();
        $requisitions = $invModel->getRequisitions();
        $myRequisitions = array_filter($requisitions, fn($r) => $r['engineer_id'] == Auth::id());

        $this->render('engineer/spares', [
            'pageTitle'    => 'My Spare Requests',
            'activeMenu'   => 'spares',
            'requisitions' => array_values($myRequisitions)
        ], 'engineer_layout');
    }

    public function history(): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
        $engineerId = Auth::id();

        $fromDate = Request::get('from_date');
        $toDate = Request::get('to_date');
        $preset = Request::get('preset', '');

        // Handle preset shortcuts
        if ($preset === 'today') {
            $fromDate = date('Y-m-d');
            $toDate = date('Y-m-d');
        } elseif ($preset === '7days') {
            $fromDate = date('Y-m-d', strtotime('-7 days'));
            $toDate = date('Y-m-d');
        } elseif ($preset === 'this_month') {
            $fromDate = date('Y-m-01');
            $toDate = date('Y-m-d');
        } elseif ($preset === '30days') {
            $fromDate = date('Y-m-d', strtotime('-30 days'));
            $toDate = date('Y-m-d');
        } elseif ($preset === 'all') {
            $fromDate = null;
            $toDate = null;
        }

        $filters = [];
        if (!empty($fromDate)) {
            $filters['from_date'] = $fromDate;
        }
        if (!empty($toDate)) {
            $filters['to_date'] = $toDate;
        }

        // By default (if no custom date filter is provided and preset is not 'all'), fetch last 10 cases
        $limit = (!empty($fromDate) || !empty($toDate) || $preset === 'all') ? null : 10;

        $completedCalls = $this->callModel->getEngineerHistory($engineerId, $filters, $limit);

        // Calculate total all-time completed count for engineer badge
        $totalAllTime = count($this->callModel->getEngineerHistory($engineerId, [], null));

        $this->render('engineer/history', [
            'pageTitle'      => 'Service History & Job Cards',
            'activeMenu'     => 'history',
            'completedCalls' => $completedCalls,
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'preset'         => $preset,
            'isDefaultLimit' => ($limit === 10),
            'totalAllTime'   => $totalAllTime
        ], 'engineer_layout');
    }

    public function printJobCard(int $id): void {
        $this->requireRoles([ROLE_ENGINEER, ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        require_once ROOT_PATH . '/controllers/CallController.php';
        $callCtrl = new CallController();
        $callCtrl->printJobCard($id);
    }
}
