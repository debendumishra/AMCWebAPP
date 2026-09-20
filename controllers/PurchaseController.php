<?php
/**
 * Purchase & Supplier Management Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Purchase.php';
require_once ROOT_PATH . '/models/Spare.php';
require_once ROOT_PATH . '/models/Inventory.php';

class PurchaseController extends Controller {
    private Purchase $purchaseModel;
    private Supplier $supplierModel;
    private Spare $spareModel;
    private Inventory $inventoryModel;

    public function __construct() {
        $this->purchaseModel = new Purchase();
        $this->supplierModel = new Supplier();
        $this->spareModel = new Spare();
        $this->inventoryModel = new Inventory();
    }

    public function index(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
        $purchases = $this->purchaseModel->getDetailedList();

        $this->render('purchases/index', [
            'pageTitle'  => 'Purchase Entries & Inward Stock',
            'activeMenu' => 'purchases',
            'purchases'  => $purchases
        ], 'admin_layout');
    }

    public function create(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $suppliers = $this->supplierModel->all(['is_active' => 1], 'name ASC');
        $spares = $this->spareModel->all(['status' => 'ACTIVE'], 'name ASC');

        $this->render('purchases/create', [
            'pageTitle'  => 'New Purchase Entry',
            'activeMenu' => 'purchases',
            'suppliers'  => $suppliers,
            'spares'     => $spares
        ], 'admin_layout');
    }

    public function store(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid security token.');
        }

        $purchaseNo = NumberGenerator::generate('purchase');
        $supplierId = (int)Request::post('supplier_id');
        $invNo = Request::post('supplier_invoice_no');
        $purchaseDate = Request::post('purchase_date', date('Y-m-d'));
        $spareItemId = (int)Request::post('spare_item_id');
        $quantity = (int)Request::post('quantity', 1);
        $rate = (float)Request::post('rate', 0.0);
        $gstRate = (float)Request::post('gst_percent', 18.0);

        $subTotal = $quantity * $rate;
        $taxAmount = ($subTotal * $gstRate) / 100;
        $totalAmount = $subTotal + $taxAmount;

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $purchaseId = $this->purchaseModel->create([
                'purchase_number'    => $purchaseNo,
                'supplier_id'        => $supplierId,
                'supplier_invoice_no'=> $invNo,
                'purchase_date'      => $purchaseDate,
                'sub_total'          => $subTotal,
                'tax_amount'         => $taxAmount,
                'total_amount'       => $totalAmount,
                'payment_status'     => 'PAID',
                'remarks'            => Request::post('remarks'),
                'created_by'         => Auth::id()
            ]);

            // Create purchase item
            $db->prepare("
                INSERT INTO purchase_items (purchase_id, spare_item_id, quantity, rate, gst_percent, total)
                VALUES (:pid, :sid, :qty, :rate, :gst, :tot)
            ")->execute([
                'pid'  => $purchaseId,
                'sid'  => $spareItemId,
                'qty'  => $quantity,
                'rate' => $rate,
                'gst'  => $gstRate,
                'tot'  => $totalAmount
            ]);

            // Add stock into double-entry inventory ledger
            $spare = $this->spareModel->find($spareItemId);
            $this->inventoryModel->recordTransaction(
                $spareItemId,
                TXN_PURCHASE,
                $quantity,
                0,
                $rate,
                'PURCHASE_INVOICE',
                $purchaseNo,
                "Inward purchase from supplier inv: {$invNo}"
            );

            $db->commit();
            $this->setFlash('success', "Purchase {$purchaseNo} recorded and {$quantity} {$spare['name']} added to inventory.");
            Response::redirect('purchases');
        } catch (Exception $e) {
            $db->rollBack();
            $this->setFlash('error', "Error saving purchase: " . $e->getMessage());
            Response::redirect('purchases/create');
        }
    }

    public function show(int $id): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
        $purchase = $this->purchaseModel->getFullPurchase($id);
        if (!$purchase) {
            Response::redirect('purchases');
        }

        $this->render('purchases/show', [
            'pageTitle'  => 'Purchase: ' . $purchase['purchase_number'],
            'activeMenu' => 'purchases',
            'purchase'   => $purchase
        ], 'admin_layout');
    }

    public function suppliers(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
        $suppliers = $this->supplierModel->all([], 'name ASC');

        $this->render('purchases/suppliers', [
            'pageTitle'  => 'Suppliers Directory',
            'activeMenu' => 'purchases',
            'suppliers'  => $suppliers
        ], 'admin_layout');
    }

    public function storeSupplier(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $name = Request::post('name');
        $mobile = Request::post('mobile');
        $address = Request::post('address');

        if (empty($name) || empty($mobile)) {
            $this->setFlash('error', 'Supplier name and mobile are required.');
            Response::redirect('purchases/suppliers');
        }

        $this->supplierModel->create([
            'name'           => $name,
            'contact_person' => Request::post('contact_person'),
            'mobile'         => $mobile,
            'email'          => Request::post('email'),
            'gstin'          => Request::post('gstin'),
            'address'        => $address,
            'city'           => Request::post('city', 'Mumbai'),
            'state'          => Request::post('state', 'Maharashtra'),
            'is_active'      => 1
        ]);

        $this->setFlash('success', "Supplier {$name} added successfully.");
        Response::redirect('purchases/suppliers');
    }
}
