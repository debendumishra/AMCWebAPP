<?php
/**
 * Machine / IT Asset Management Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Machine.php';
require_once ROOT_PATH . '/models/Customer.php';

class MachineController extends Controller {
    private Machine $machineModel;
    private Customer $customerModel;

    public function __construct() {
        $this->machineModel = new Machine();
        $this->customerModel = new Customer();
    }

    public function index(): void {
        $this->requireAuth();
        $machines = $this->machineModel->getDetailedList();

        $this->render('machines/index', [
            'pageTitle'  => 'Machine & IT Asset Inventory',
            'activeMenu' => 'machines',
            'machines'   => $machines
        ], 'admin_layout');
    }

    public function create(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $db = Database::getInstance();
        $customers = $this->customerModel->all([], 'company_name ASC');
        $assetTypes = $db->query("SELECT * FROM asset_types WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

        $selectedCustomerId = Request::get('customer_id');
        $locations = [];
        if ($selectedCustomerId) {
            $locModel = new CustomerLocation();
            $locations = $locModel->getByCustomerId((int)$selectedCustomerId);
        }

        $this->render('machines/create', [
            'pageTitle'          => 'Register New Asset',
            'activeMenu'         => 'machines',
            'customers'          => $customers,
            'assetTypes'         => $assetTypes,
            'selectedCustomerId' => $selectedCustomerId,
            'locations'          => $locations
        ], 'admin_layout');
    }

    public function store(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            $this->setFlash('error', 'Invalid security token.');
            Response::redirect('machines/create');
        }

        $assetCode = NumberGenerator::generate('asset');
        $customerId = (int)Request::post('customer_id');
        $locationId = (int)Request::post('location_id');
        $typeId = (int)Request::post('asset_type_id');
        $make = Request::post('make');
        $model = Request::post('model');
        $serial = Request::post('serial_number');

        if (empty($customerId) || empty($locationId) || empty($make) || empty($serial)) {
            $this->setFlash('error', 'Customer, location, make, and serial number are required.');
            Response::redirect('machines/create');
        }

        // Generate unique secure QR code token
        $qrToken = 'QR-' . strtoupper(substr($make, 0, 2)) . '-' . substr($serial, -6) . '-' . bin2hex(random_bytes(3));

        $machineId = $this->machineModel->create([
            'asset_code'        => $assetCode,
            'asset_tag'         => Request::post('asset_tag'),
            'customer_id'       => $customerId,
            'location_id'       => $locationId,
            'asset_type_id'     => $typeId,
            'building'          => Request::post('building'),
            'floor'             => Request::post('floor'),
            'department'        => Request::post('department'),
            'assigned_employee' => Request::post('assigned_employee'),
            'make'              => $make,
            'model'             => $model,
            'serial_number'     => $serial,
            'processor'         => Request::post('processor'),
            'ram'               => Request::post('ram'),
            'storage'           => Request::post('storage'),
            'operating_system'  => Request::post('operating_system'),
            'ip_address'        => Request::post('ip_address'),
            'mac_address'       => Request::post('mac_address'),
            'status'            => 'ACTIVE',
            'qr_code_token'     => $qrToken,
            'remarks'           => Request::post('remarks')
        ]);

        $this->setFlash('success', "Asset {$make} {$model} ({$assetCode}) registered with QR token.");
        Response::redirect('machines/' . $machineId);
    }

    public function show(int $id): void {
        $this->requireAuth();
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT m.*, c.company_name, c.customer_code, c.mobile as customer_mobile, c.email as customer_email,
                   cl.location_name, cl.address as location_address, cl.city,
                   at.name as asset_type_name, at.icon as asset_type_icon,
                   ct.contract_number, ct.title as contract_title, ct.status as contract_status,
                   cty.name as contract_type_name, cty.is_spares_covered, cty.is_labour_covered
            FROM machines m
            JOIN customers c ON m.customer_id = c.id
            JOIN customer_locations cl ON m.location_id = cl.id
            JOIN asset_types at ON m.asset_type_id = at.id
            LEFT JOIN contract_machines cm ON cm.machine_id = m.id AND cm.status = 'ACTIVE'
            LEFT JOIN contracts ct ON cm.contract_id = ct.id AND ct.status = 'ACTIVE'
            LEFT JOIN contract_types cty ON ct.contract_type_id = cty.id
            WHERE m.id = :id AND m.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $machine = $stmt->fetch();

        if (!$machine) {
            Response::redirect('machines');
        }

        $serviceHistory = $this->machineModel->getServiceHistory($id);

        $this->render('machines/show', [
            'pageTitle'      => $machine['make'] . ' ' . $machine['model'] . ' (' . $machine['asset_code'] . ')',
            'activeMenu'     => 'machines',
            'machine'        => $machine,
            'serviceHistory' => $serviceHistory
        ], 'admin_layout');
    }

    public function viewByQrToken(string $token): void {
        $token = trim(urldecode($token));
        if (preg_match('~machines/qr/([^/?#]+)~i', $token, $m)) {
            $token = trim($m[1]);
        }

        $machine = $this->machineModel->findByQrToken($token);
        
        $role = Auth::role();
        $layout = ($role === ROLE_ENGINEER) ? 'engineer_layout' : (($role === ROLE_CUSTOMER) ? 'customer_layout' : 'admin_layout');

        if (!$machine) {
            $this->render('machines/qr_not_found', [
                'pageTitle'  => 'Asset Not Found',
                'activeMenu' => 'machines',
                'scannedCode' => $token
            ], $layout);
            return;
        }

        $serviceHistory = $this->machineModel->getServiceHistory((int)$machine['id']);

        $this->render('machines/qr', [
            'pageTitle'      => 'QR Asset: ' . $machine['asset_code'],
            'activeMenu'     => 'machines',
            'machine'        => $machine,
            'serviceHistory' => $serviceHistory
        ], $layout);
    }

    public function ajaxSearch(): void {
        $this->requireAuth();
        $query = trim(Request::get('q', ''));
        $qrToken = trim(Request::get('qr', Request::get('token', '')));
        $customerId = Request::get('customer_id');
        $locationId = Request::get('location_id');
        $limit = min(1000, max(1, (int)Request::get('limit', 100)));

        if (Auth::role() === ROLE_CUSTOMER) {
            $user = Auth::user();
            $customerId = (int)($user['customer_id'] ?? 1);
        }

        if (!empty($qrToken)) {
            $machine = $this->machineModel->findByQrToken($qrToken);
            Response::json([
                'success'  => (bool)$machine,
                'count'    => $machine ? 1 : 0,
                'machine'  => $machine,
                'results'  => $machine ? [$machine] : [],
                'machines' => $machine ? [$machine] : []
            ]);
            return;
        }

        $results = $this->machineModel->searchAssets(
            $query,
            $customerId ? (int)$customerId : null,
            $limit,
            $locationId ? (int)$locationId : null
        );

        Response::json([
            'success'  => true,
            'count'    => count($results),
            'results'  => $results,
            'machines' => $results
        ]);
    }
}
