<?php
/**
 * Customer Self-Service Portal Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Customer.php';
require_once ROOT_PATH . '/models/Machine.php';
require_once ROOT_PATH . '/models/Call.php';
require_once ROOT_PATH . '/models/Contract.php';
require_once ROOT_PATH . '/models/Invoice.php';

class CustomerPortalController extends Controller {
    private int $customerId;
    private Customer $customerModel;
    private Machine $machineModel;
    private Call $callModel;

    public function __construct() {
        $this->requireRoles([ROLE_CUSTOMER]);
        $user = Auth::user();
        $this->customerId = (int)($user['customer_id'] ?? 1);
        $this->customerModel = new Customer();
        $this->machineModel = new Machine();
        $this->callModel = new Call();
    }

    public function index(): void {
        $customer = $this->customerModel->find($this->customerId);
        $machines = $this->machineModel->getDetailedList(['customer_id' => $this->customerId]);
        $calls = $this->callModel->getDetailedList(['customer_id' => $this->customerId]);

        $openCalls = array_filter($calls, fn($c) => !in_array($c['status'], ['RESOLVED', 'CLOSED', 'CANCELLED']));

        $db = Database::getInstance();
        $invStmt = $db->prepare("SELECT * FROM invoices WHERE customer_id = :cid ORDER BY id DESC LIMIT 5");
        $invStmt->execute(['cid' => $this->customerId]);
        $invoices = $invStmt->fetchAll();

        $this->render('customer/index', [
            'pageTitle'  => 'Client Portal Dashboard',
            'activeMenu' => 'dashboard',
            'customer'   => $customer,
            'machines'   => $machines,
            'openCalls'  => array_values($openCalls),
            'invoices'   => $invoices
        ], 'customer_layout');
    }

    public function machines(): void {
        $machines = $this->machineModel->getDetailedList(['customer_id' => $this->customerId]);

        $this->render('customer/machines', [
            'pageTitle'  => 'My Hardware Assets',
            'activeMenu' => 'machines',
            'machines'   => $machines
        ], 'customer_layout');
    }

    public function calls(): void {
        $calls = $this->callModel->getDetailedList(['customer_id' => $this->customerId]);

        $this->render('customer/calls', [
            'pageTitle'  => 'My Service Calls',
            'activeMenu' => 'calls',
            'calls'      => $calls
        ], 'customer_layout');
    }

    public function createCall(): void {
        $machines = $this->machineModel->getDetailedList(['customer_id' => $this->customerId]);
        $locModel = new CustomerLocation();
        $locations = $locModel->getByCustomerId($this->customerId);

        $db = Database::getInstance();
        $categories = $db->query("SELECT * FROM problem_categories WHERE is_active = 1")->fetchAll();

        $this->render('customer/create_call', [
            'pageTitle'  => 'Raise Service Request',
            'activeMenu' => 'create_call',
            'machines'   => $machines,
            'locations'  => $locations,
            'categories' => $categories
        ], 'customer_layout');
    }

    public function storeCall(): void {
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $callNo = NumberGenerator::generate('call');
        $locationId = (int)Request::post('location_id');
        $machineId = Request::post('machine_id') ? (int)Request::post('machine_id') : null;
        $reportedIssue = Request::post('reported_issue');
        $user = Auth::user();

        $this->callModel->create([
            'call_number'            => $callNo,
            'call_type'              => 'AMC',
            'priority'               => Request::post('priority', 'MEDIUM'),
            'customer_id'            => $this->customerId,
            'location_id'            => $locationId,
            'machine_id'             => $machineId,
            'caller_name'            => $user['name'],
            'caller_mobile'          => $user['mobile'] ?? '9876543210',
            'reported_issue'         => $reportedIssue,
            'status'                 => STATUS_NEW,
            'sla_response_deadline'  => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'sla_resolution_deadline'=> date('Y-m-d H:i:s', strtotime('+8 hours')),
            'created_by'             => Auth::id()
        ]);

        $this->setFlash('success', "Service complaint {$callNo} registered. A field technician will be assigned shortly.");
        Response::redirect('customer/calls');
    }

    public function invoices(): void {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM invoices WHERE customer_id = :cid ORDER BY id DESC");
        $stmt->execute(['cid' => $this->customerId]);
        $invoices = $stmt->fetchAll();

        $this->render('customer/invoices', [
            'pageTitle'  => 'Invoices & Billing',
            'activeMenu' => 'invoices',
            'invoices'   => $invoices
        ], 'customer_layout');
    }
}
