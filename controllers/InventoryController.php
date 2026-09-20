<?php
/**
 * Inventory, Spare Parts & Approval Queue Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Spare.php';
require_once ROOT_PATH . '/models/Call.php';

class InventoryController extends Controller {
    private Spare $spareModel;
    private Inventory $inventoryModel;

    public function __construct() {
        $this->spareModel = new Spare();
        $this->inventoryModel = new Inventory();
    }

    public function index(): void {
        $this->requireAuth();
        $spares = $this->spareModel->getDetailedList();

        $this->render('inventory/index', [
            'pageTitle'  => 'Spare Parts & Inventory Ledger',
            'activeMenu' => 'inventory',
            'spares'     => $spares
        ], 'admin_layout');
    }

    public function create(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $db = Database::getInstance();
        $categories = $db->query("SELECT * FROM spare_categories WHERE is_active = 1")->fetchAll();
        $brands = $db->query("SELECT * FROM spare_brands WHERE is_active = 1")->fetchAll();

        $this->render('inventory/create', [
            'pageTitle'  => 'Add New Spare Part Master',
            'activeMenu' => 'inventory',
            'categories' => $categories,
            'brands'     => $brands
        ], 'admin_layout');
    }

    public function store(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid security token.');
        }

        $name = Request::post('name');
        $sku = Request::post('sku');
        $categoryId = (int)Request::post('category_id');
        $brandId = Request::post('brand_id') ? (int)Request::post('brand_id') : null;
        $purchasePrice = (float)Request::post('purchase_price', 0.0);
        $sellingPrice = (float)Request::post('selling_price', 0.0);
        $openingStock = (int)Request::post('opening_stock', 0);
        $minStock = (int)Request::post('min_stock_level', 5);

        if (empty($name) || empty($sku) || empty($categoryId)) {
            $this->setFlash('error', 'Item name, SKU, and category are required.');
            Response::redirect('inventory/create');
        }

        $spareId = $this->spareModel->create([
            'category_id'     => $categoryId,
            'brand_id'        => $brandId,
            'sku'             => $sku,
            'name'            => $name,
            'capacity_spec'   => Request::post('capacity_spec'),
            'unit'            => Request::post('unit', 'PCS'),
            'hsn_code'        => Request::post('hsn_code', '8473'),
            'purchase_price'  => $purchasePrice,
            'selling_price'   => $sellingPrice,
            'gst_rate'        => (float)Request::post('gst_rate', 18.0),
            'min_stock_level' => $minStock,
            'max_stock_level' => (int)Request::post('max_stock_level', 50),
            'status'          => 'ACTIVE'
        ]);

        // Record opening stock if > 0
        if ($openingStock > 0) {
            $this->inventoryModel->recordTransaction(
                $spareId,
                TXN_OPENING,
                $openingStock,
                0,
                $purchasePrice,
                'OPENING_BALANCE',
                'OPN-' . $spareId,
                'Opening stock balance initialization'
            );
        }

        $this->setFlash('success', "Spare item {$name} ({$sku}) created successfully.");
        Response::redirect('inventory');
    }

    public function requisitions(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
        $requisitions = $this->inventoryModel->getRequisitions();

        $this->render('inventory/requisitions', [
            'pageTitle'    => 'Spare Requisitions & Approval Queue',
            'activeMenu'   => 'requisitions',
            'requisitions' => $requisitions
        ], 'admin_layout');
    }

    public function approveRequisition(int $id): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $req = $this->inventoryModel->getRequisitionDetails($id);
        if (!$req) {
            $this->setFlash('error', 'Requisition not found.');
            Response::redirect('inventory/requisitions');
        }

        $db = Database::getInstance();

        try {
            if (!$db->inTransaction()) {
                $db->beginTransaction();
            }

            foreach ($req['items'] as $item) {
                $qty = (int)$item['requested_qty'];
                $spareItemId = (int)$item['spare_item_id'];

                // Deduct stock via immutable transaction
                $success = $this->inventoryModel->recordTransaction(
                    $spareItemId,
                    TXN_ISSUE,
                    0,
                    $qty,
                    (float)$item['selling_price'],
                    'SPARE_REQ',
                    $req['request_number'],
                    "Issued for Call {$req['call_number']} to {$req['engineer_name']}"
                );

                if (!$success) {
                    throw new Exception("Insufficient warehouse stock for item: {$item['spare_name']}");
                }

                // Update item status
                $db->prepare("UPDATE spare_request_items SET approved_qty = :qty, issued_qty = :qty2, status = 'ISSUED' WHERE id = :id")
                   ->execute(['qty' => $qty, 'qty2' => $qty, 'id' => $item['id']]);
            }

            // Update spare_requests
            $db->prepare("
                UPDATE spare_requests 
                SET status = 'APPROVED', approved_by = :uid, approved_at = NOW(), approval_remarks = :rem 
                WHERE id = :id
            ")->execute([
                'uid' => Auth::id(),
                'rem' => Request::post('remarks', 'Approved by Operations Manager'),
                'id'  => $id
            ]);

            // Update call status
            $callModel = new Call();
            $callModel->transitionStatus($req['call_id'], STATUS_SPARE_ISSUED, "Spare part approved & issued from main inventory ({$req['request_number']})");

            if ($db->inTransaction()) {
                $db->commit();
            }
            $this->setFlash('success', "Spare requisition {$req['request_number']} approved and inventory deducted successfully.");
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->setFlash('error', "Approval error: " . $e->getMessage());
        }

        Response::redirect('inventory/requisitions');
    }

    public function rejectRequisition(int $id): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $db = Database::getInstance();
        $db->prepare("
            UPDATE spare_requests 
            SET status = 'REJECTED', approved_by = :uid, approved_at = NOW(), approval_remarks = :rem 
            WHERE id = :id
        ")->execute([
            'uid' => Auth::id(),
            'rem' => Request::post('remarks', 'Rejection reason: Part not covered or unavailable'),
            'id'  => $id
        ]);

        $this->setFlash('success', "Spare requisition rejected.");
        Response::redirect('inventory/requisitions');
    }

    public function ledger(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $ledgerEntries = $this->inventoryModel->getLedgerHistory(100);

        $this->render('inventory/ledger', [
            'pageTitle'     => 'Double-Entry Stock Ledger',
            'activeMenu'    => 'inventory',
            'ledgerEntries' => $ledgerEntries
        ], 'admin_layout');
    }
}
