<?php
/**
 * AMC Contract Management Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Contract.php';
require_once ROOT_PATH . '/models/Customer.php';
require_once ROOT_PATH . '/models/Machine.php';

class ContractController extends Controller {
    private Contract $contractModel;
    private Customer $customerModel;

    public function __construct() {
        $this->contractModel = new Contract();
        $this->customerModel = new Customer();
    }

    public function index(): void {
        $this->requireAuth();
        $contracts = $this->contractModel->getDetailedList();

        $this->render('contracts/index', [
            'pageTitle'  => 'AMC Contracts Management',
            'activeMenu' => 'contracts',
            'contracts'  => $contracts
        ], 'admin_layout');
    }

    public function create(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $db = Database::getInstance();
        $customers = $this->customerModel->all([], 'company_name ASC');
        $contractTypes = $db->query("SELECT * FROM contract_types WHERE is_active = 1")->fetchAll();

        $this->render('contracts/create', [
            'pageTitle'     => 'Create New AMC Contract',
            'activeMenu'    => 'contracts',
            'customers'     => $customers,
            'contractTypes' => $contractTypes
        ], 'admin_layout');
    }

    public function store(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            $this->setFlash('error', 'Invalid CSRF token.');
            Response::redirect('contracts/create');
        }

        $contractNo = NumberGenerator::generate('contract');
        $customerId = (int)Request::post('customer_id');
        $typeId = (int)Request::post('contract_type_id');
        $title = Request::post('title');
        $startDate = Request::post('start_date');
        $endDate = Request::post('end_date');
        $value = (float)Request::post('contract_value', 0.0);

        if (empty($customerId) || empty($typeId) || empty($title) || empty($startDate) || empty($endDate)) {
            $this->setFlash('error', 'Please fill in all mandatory contract fields.');
            Response::redirect('contracts/create');
        }

        $contractId = $this->contractModel->create([
            'contract_number'       => $contractNo,
            'customer_id'           => $customerId,
            'contract_type_id'      => $typeId,
            'title'                 => $title,
            'start_date'            => $startDate,
            'end_date'              => $endDate,
            'contract_value'        => $value,
            'billing_frequency'     => Request::post('billing_frequency', 'ANNUAL'),
            'payment_terms'         => Request::post('payment_terms', 'Advance'),
            'gst_rate'              => (float)Request::post('gst_rate', 18.0),
            'response_time_hrs'     => (int)Request::post('response_time_hrs', 4),
            'resolution_time_hrs'   => (int)Request::post('resolution_time_hrs', 24),
            'renewal_reminder_days' => (int)Request::post('renewal_reminder_days', 30),
            'status'                => 'ACTIVE',
            'remarks'               => Request::post('remarks'),
            'created_by'            => Auth::id()
        ]);

        $this->setFlash('success', "Contract {$title} ({$contractNo}) created successfully.");
        Response::redirect('contracts/' . $contractId);
    }

    public function show(int $id): void {
        $this->requireAuth();
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT ct.*, c.company_name, c.customer_code, c.mobile as customer_mobile, c.email as customer_email,
                   cty.name as contract_type_name, cty.is_spares_covered, cty.is_labour_covered, cty.pm_frequency
            FROM contracts ct
            JOIN customers c ON ct.customer_id = c.id
            JOIN contract_types cty ON ct.contract_type_id = cty.id
            WHERE ct.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $contract = $stmt->fetch();

        if (!$contract) {
            Response::redirect('contracts');
        }

        $contractMachines = $this->contractModel->getContractMachines($id);

        // Fetch unassigned machines belonging to this customer for quick mapping
        $unassignedStmt = $db->prepare("
            SELECT m.*, at.name as asset_type_name, cl.location_name
            FROM machines m
            JOIN asset_types at ON m.asset_type_id = at.id
            JOIN customer_locations cl ON m.location_id = cl.id
            WHERE m.customer_id = :cid AND m.deleted_at IS NULL AND m.id NOT IN (
                SELECT machine_id FROM contract_machines WHERE contract_id = :ctid
            )
        ");
        $unassignedStmt->execute(['cid' => $contract['customer_id'], 'ctid' => $id]);
        $unassignedMachines = $unassignedStmt->fetchAll();

        $this->render('contracts/show', [
            'pageTitle'          => 'AMC Contract: ' . $contract['contract_number'],
            'activeMenu'         => 'contracts',
            'contract'           => $contract,
            'contractMachines'   => $contractMachines,
            'unassignedMachines' => $unassignedMachines
        ], 'admin_layout');
    }

    public function assignMachines(int $contractId): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $machineIds = Request::post('machine_ids');
        if (!is_array($machineIds) || empty($machineIds)) {
            $this->setFlash('error', 'Please select at least one machine to map to this contract.');
            Response::redirect('contracts/' . $contractId);
        }

        $contract = $this->contractModel->find($contractId);
        $db = Database::getInstance();

        foreach ($machineIds as $mid) {
            $mIdInt = (int)$mid;
            $db->prepare("
                INSERT INTO contract_machines (contract_id, machine_id, start_date, end_date, status)
                VALUES (:cid, :mid, :start, :end, 'ACTIVE')
                ON DUPLICATE KEY UPDATE status = 'ACTIVE'
            ")->execute([
                'cid'   => $contractId,
                'mid'   => $mIdInt,
                'start' => $contract['start_date'],
                'end'   => $contract['end_date']
            ]);

            // Update machine status to UNDER_AMC
            $db->prepare("UPDATE machines SET status = 'UNDER_AMC' WHERE id = :mid")->execute(['mid' => $mIdInt]);
        }

        // Auto-generate recurring PM visit schedules for this contract
        require_once ROOT_PATH . '/models/PM.php';
        $pmCount = (new PM())->generateSchedulesForContract($contractId);

        $msg = count($machineIds) . " machine(s) mapped to AMC Contract {$contract['contract_number']}.";
        if ($pmCount > 0) {
            $msg .= " Automatically scheduled {$pmCount} Preventive Maintenance visit(s).";
        }

        $this->setFlash('success', $msg);
        Response::redirect('contracts/' . $contractId);
    }
}
