<?php
/**
 * Customer Management Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Customer.php';
require_once ROOT_PATH . '/models/Machine.php';
require_once ROOT_PATH . '/models/Contract.php';
require_once ROOT_PATH . '/models/Call.php';

class CustomerController extends Controller {
    private Customer $customerModel;
    private CustomerLocation $locationModel;

    public function __construct() {
        $this->customerModel = new Customer();
        $this->locationModel = new CustomerLocation();
    }

    public function index(): void {
        $this->requireAuth();
        $customers = $this->customerModel->getSummaryList();

        $this->render('customers/index', [
            'pageTitle'  => 'Customers & Service Locations',
            'activeMenu' => 'customers',
            'customers'  => $customers
        ], 'admin_layout');
    }

    public function create(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $this->render('customers/create', [
            'pageTitle'  => 'Add New Customer',
            'activeMenu' => 'customers'
        ], 'admin_layout');
    }

    public function store(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            $this->setFlash('error', 'Invalid CSRF token.');
            Response::redirect('customers/create');
        }

        $code = NumberGenerator::generate('customer');
        $company = Request::post('company_name');
        $person = Request::post('contact_person');
        $mobile = Request::post('mobile');
        $email = Request::post('email');
        $address = Request::post('billing_address');
        $city = Request::post('city', 'Mumbai');
        $state = Request::post('state', 'Maharashtra');
        $pincode = Request::post('pincode', '400001');
        $type = Request::post('customer_type', 'AMC');
        $gstin = Request::post('gstin');
        $pan = Request::post('pan');

        if (empty($company) || empty($mobile) || empty($email)) {
            $this->setFlash('error', 'Company name, mobile, and email are required.');
            Response::redirect('customers/create');
        }

        $customerId = $this->customerModel->create([
            'customer_code'   => $code,
            'company_name'    => $company,
            'contact_person'  => $person,
            'designation'     => Request::post('designation', 'Manager'),
            'mobile'          => $mobile,
            'alternate_mobile'=> Request::post('alternate_mobile'),
            'email'           => $email,
            'gstin'           => $gstin,
            'pan'             => $pan,
            'billing_address' => $address,
            'city'            => $city,
            'state'           => $state,
            'pincode'         => $pincode,
            'customer_type'   => $type,
            'status'          => 'ACTIVE'
        ]);

        // Create default Primary Head Office Location
        $this->locationModel->create([
            'customer_id'   => $customerId,
            'location_name' => Request::post('location_name', 'Head Office'),
            'address'       => $address,
            'city'          => $city,
            'state'         => $state,
            'pincode'       => $pincode,
            'contact_person'=> $person,
            'mobile'        => $mobile,
            'email'         => $email
        ]);

        $this->setFlash('success', "Customer {$company} ($code) created successfully with default location.");
        Response::redirect('customers/' . $customerId);
    }

    public function show(int $id): void {
        $this->requireAuth();
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            Response::redirect('customers');
        }

        $locations = $this->locationModel->getByCustomerId($id);
        
        $machineModel = new Machine();
        $machines = $machineModel->getDetailedList(['customer_id' => $id]);

        $callModel = new Call();
        $calls = $callModel->getDetailedList(['customer_id' => $id]);

        $this->render('customers/show', [
            'pageTitle'  => $customer['company_name'] . ' Profile',
            'activeMenu' => 'customers',
            'customer'   => $customer,
            'locations'  => $locations,
            'machines'   => $machines,
            'calls'      => $calls
        ], 'admin_layout');
    }

    public function addLocation(int $customerId): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid security token.');
        }

        $locationName = Request::post('location_name');
        $address = Request::post('address');
        $city = Request::post('city');
        $state = Request::post('state', 'Maharashtra');
        $pincode = Request::post('pincode');
        $contactPerson = Request::post('contact_person');
        $mobile = Request::post('mobile');
        $email = Request::post('email');
        $lat = Request::post('latitude');
        $lng = Request::post('longitude');

        if (empty($locationName) || empty($address) || empty($city)) {
            $this->setFlash('error', 'Location name, address, and city are mandatory.');
            Response::redirect('customers/' . $customerId);
        }

        $this->locationModel->create([
            'customer_id'   => $customerId,
            'location_name' => $locationName,
            'address'       => $address,
            'city'          => $city,
            'state'         => $state,
            'pincode'       => $pincode,
            'contact_person'=> $contactPerson,
            'mobile'        => $mobile,
            'email'         => $email,
            'latitude'      => !empty($lat) ? $lat : null,
            'longitude'     => !empty($lng) ? $lng : null
        ]);

        $this->setFlash('success', "New branch location '{$locationName}' added successfully.");
        Response::redirect('customers/' . $customerId);
    }

    public function ajaxLocations(): void {
        $customerId = (int)Request::get('customer_id');
        if (!$customerId) {
            Response::json(['locations' => []]);
        }
        $locations = $this->locationModel->getByCustomerId($customerId);
        Response::json(['locations' => $locations]);
    }
}
