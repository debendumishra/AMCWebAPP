<?php
/**
 * Billing, GST Invoicing & Payment Receipts Controller
 */

defined('APP_INIT') or define('APP_INIT', true);

require_once ROOT_PATH . '/models/Invoice.php';
require_once ROOT_PATH . '/models/Customer.php';
require_once ROOT_PATH . '/models/Contract.php';
require_once ROOT_PATH . '/models/Call.php';
require_once ROOT_PATH . '/models/Spare.php';
require_once ROOT_PATH . '/models/Setting.php';

class BillingController extends Controller {
    private Invoice $invoiceModel;
    private Customer $customerModel;

    public function __construct() {
        $this->invoiceModel = new Invoice();
        $this->customerModel = new Customer();
    }

    public function index(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
        $invoices = $this->invoiceModel->getDetailedList();

        $this->render('billing/index', [
            'pageTitle'  => 'Invoices & Billing Register',
            'activeMenu' => 'billing',
            'invoices'   => $invoices
        ], 'admin_layout');
    }

    public function create(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        $customers = $this->customerModel->all([], 'company_name ASC');
        $contracts = (new Contract())->all(['status' => 'ACTIVE'], 'contract_number ASC');
        $spares = (new Spare())->all(['status' => 'ACTIVE'], 'name ASC');

        $this->render('billing/create', [
            'pageTitle'  => 'Create Tax Invoice',
            'activeMenu' => 'billing',
            'customers'  => $customers,
            'contracts'  => $contracts,
            'spares'     => $spares
        ], 'admin_layout');
    }

    public function store(): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $customerId = (int)Request::post('customer_id');
        $contractId = Request::post('contract_id') ? (int)Request::post('contract_id') : null;
        $invType = Request::post('invoice_type', 'AMC');
        $invDate = Request::post('invoice_date', date('Y-m-d'));
        $dueDate = Request::post('due_date', date('Y-m-d', strtotime('+15 days')));
        $notes = Request::post('notes');

        if (empty($customerId)) {
            $this->setFlash('error', 'Please select a customer for the invoice.');
            Response::redirect('billing/create');
        }

        $items = Request::post('items');
        if (!is_array($items) || empty($items)) {
            if (Request::post('description')) {
                $items = [[
                    'description' => Request::post('description'),
                    'hsn_sac'     => Request::post('hsn_sac', '9987'),
                    'quantity'    => (int)Request::post('quantity', 1),
                    'unit_price'  => (float)Request::post('unit_price', 0.0),
                    'gst_rate'    => (float)Request::post('gst_rate', 18.0)
                ]];
            } else {
                $this->setFlash('error', 'Please add at least one line item to the invoice.');
                Response::redirect('billing/create');
            }
        }

        $validItems = [];
        $totalSubTotal = 0.0;
        $totalCgst = 0.0;
        $totalSgst = 0.0;

        foreach ($items as $item) {
            $desc = trim($item['description'] ?? '');
            if (empty($desc)) continue;

            $qty = max(1, (int)($item['quantity'] ?? 1));
            $price = max(0.0, (float)($item['unit_price'] ?? 0.0));
            $gstRate = (float)($item['gst_rate'] ?? 18.0);
            $hsnSac = trim($item['hsn_sac'] ?? '9987');

            $lineSubTotal = $qty * $price;
            $halfGst = $gstRate / 2.0;
            $lineCgst = ($lineSubTotal * $halfGst) / 100.0;
            $lineSgst = ($lineSubTotal * $halfGst) / 100.0;
            $lineTotal = $lineSubTotal + $lineCgst + $lineSgst;

            $totalSubTotal += $lineSubTotal;
            $totalCgst += $lineCgst;
            $totalSgst += $lineSgst;

            $validItems[] = [
                'description' => $desc,
                'hsn_sac'     => $hsnSac,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'gst_rate'    => $gstRate,
                'cgst'        => $lineCgst,
                'sgst'        => $lineSgst,
                'total'       => $lineTotal
            ];
        }

        if (empty($validItems)) {
            $this->setFlash('error', 'Please provide valid item descriptions and prices.');
            Response::redirect('billing/create');
        }

        $grandTotal = $totalSubTotal + $totalCgst + $totalSgst;
        $invNo = NumberGenerator::generate('invoice');

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $invId = $this->invoiceModel->create([
                'invoice_number' => $invNo,
                'customer_id'    => $customerId,
                'contract_id'    => $contractId,
                'invoice_type'   => $invType,
                'invoice_date'   => $invDate,
                'due_date'       => $dueDate,
                'sub_total'      => $totalSubTotal,
                'cgst_amount'    => $totalCgst,
                'sgst_amount'    => $totalSgst,
                'total_amount'   => $grandTotal,
                'paid_amount'    => 0.0,
                'balance_amount' => $grandTotal,
                'status'         => 'SENT',
                'notes'          => $notes,
                'created_by'     => Auth::id()
            ]);

            $itemStmt = $db->prepare("
                INSERT INTO invoice_items (invoice_id, description, hsn_sac, quantity, unit_price, gst_rate, cgst, sgst, total)
                VALUES (:iid, :desc, :hsn, :qty, :price, :gst, :cgst, :sgst, :tot)
            ");

            foreach ($validItems as $vItem) {
                $itemStmt->execute([
                    'iid'   => $invId,
                    'desc'  => $vItem['description'],
                    'hsn'   => $vItem['hsn_sac'],
                    'qty'   => $vItem['quantity'],
                    'price' => $vItem['unit_price'],
                    'gst'   => $vItem['gst_rate'],
                    'cgst'  => $vItem['cgst'],
                    'sgst'  => $vItem['sgst'],
                    'tot'   => $vItem['total']
                ]);
            }

            $db->commit();
            $this->setFlash('success', "Tax Invoice {$invNo} generated successfully with " . count($validItems) . " line item(s).");
            Response::redirect('billing/' . $invId);
        } catch (Exception $e) {
            $db->rollBack();
            $this->setFlash('error', "Error generating invoice: " . $e->getMessage());
            Response::redirect('billing/create');
        }
    }

    public function show(int $id): void {
        $this->requireAuth();
        $invoice = $this->invoiceModel->getFullInvoice($id);
        if (!$invoice) {
            Response::redirect('billing');
        }

        $this->render('billing/show', [
            'pageTitle'  => 'Invoice: ' . $invoice['invoice_number'],
            'activeMenu' => 'billing',
            'invoice'    => $invoice
        ], 'admin_layout');
    }

    public function recordPayment(int $invoiceId): void {
        $this->requireRoles([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
        if (!Request::verifyCsrf()) {
            Response::error('Invalid CSRF token.');
        }

        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            Response::redirect('billing');
        }

        $amount = (float)Request::post('amount', 0.0);
        $mode = Request::post('payment_mode', 'UPI');
        $ref = Request::post('transaction_reference');
        $bank = Request::post('bank_name');
        $remarks = Request::post('remarks');

        if ($amount <= 0) {
            $this->setFlash('error', 'Please enter a valid payment amount.');
            Response::redirect('billing/' . $invoiceId);
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $payNo = 'PAY-' . date('Y') . '-' . str_pad((string)random_int(1, 99999), 6, '0', STR_PAD_LEFT);

            $db->prepare("
                INSERT INTO payments (payment_number, invoice_id, customer_id, payment_date, amount, payment_mode, transaction_reference, bank_name, remarks, received_by, created_at)
                VALUES (:pno, :iid, :cid, :pdate, :amt, :mode, :ref, :bank, :rem, :uid, NOW())
            ")->execute([
                'pno'   => $payNo,
                'iid'   => $invoiceId,
                'cid'   => $invoice['customer_id'],
                'pdate' => Request::post('payment_date', date('Y-m-d')),
                'amt'   => $amount,
                'mode'  => $mode,
                'ref'   => $ref,
                'bank'  => $bank,
                'rem'   => $remarks,
                'uid'   => Auth::id()
            ]);

            // Update invoice balances
            $newPaid = (float)$invoice['paid_amount'] + $amount;
            $newBalance = max(0.0, (float)$invoice['total_amount'] - $newPaid);
            $newStatus = ($newBalance <= 0) ? 'PAID' : 'PARTIALLY_PAID';

            $this->invoiceModel->update($invoiceId, [
                'paid_amount'    => $newPaid,
                'balance_amount' => $newBalance,
                'status'         => $newStatus
            ]);

            $db->commit();
            $this->setFlash('success', "Payment receipt of ₹" . number_format($amount, 2) . " ({$payNo}) recorded.");
        } catch (Exception $e) {
            $db->rollBack();
            $this->setFlash('error', "Error recording payment: " . $e->getMessage());
        }

        Response::redirect('billing/' . $invoiceId);
    }

    public function printInvoice(int $id): void {
        $invoice = $this->invoiceModel->getFullInvoice($id);
        if (!$invoice) {
            die("Invoice not found.");
        }

        $company = [
            'name'    => Setting::get('company_name', 'Apex IT Solutions & AMC Services'),
            'gstin'   => Setting::get('company_gstin', '27AAACA1234A1Z5'),
            'address' => Setting::get('company_address', 'Tower B, Tech Park, Mumbai, MH'),
            'phone'   => Setting::get('company_phone', '+91 98765 43210'),
            'email'   => Setting::get('company_email', 'billing@amc.com')
        ];

        $this->render('billing/print', [
            'pageTitle' => 'Tax Invoice ' . $invoice['invoice_number'],
            'invoice'   => $invoice,
            'company'   => $company
        ], 'none');
    }
}
