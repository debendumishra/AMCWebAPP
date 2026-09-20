<?php
/**
 * Professional Printable Tax Invoice
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - <?= $invoice['invoice_number'] ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #fff; color: #1e293b; font-size: 13px; }
        .invoice-box { max-width: 850px; margin: 20px auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 8px; }
        @media print {
            .no-print { display: none !important; }
            .invoice-box { border: none; padding: 0; }
        }
    </style>
</head>
<body>
<div class="text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold"><i class="bi bi-printer"></i> Print / Save as PDF</button>
</div>

<div class="invoice-box">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col-7">
            <h3 class="fw-bold text-primary mb-1"><?= htmlspecialchars($company['name']) ?></h3>
            <p class="text-muted small mb-0">
                <?= htmlspecialchars($company['address']) ?><br>
                Phone: <?= $company['phone'] ?> | Email: <?= $company['email'] ?><br>
                <strong>GSTIN: <?= $company['gstin'] ?></strong>
            </p>
        </div>
        <div class="col-5 text-end">
            <h4 class="fw-bold text-uppercase tracking-wide text-dark mb-1">TAX INVOICE</h4>
            <div class="fs-6 fw-bold text-primary"><?= $invoice['invoice_number'] ?></div>
            <div class="small text-muted">Date: <strong><?= $invoice['invoice_date'] ?></strong></div>
            <div class="small text-muted">Due Date: <strong><?= $invoice['due_date'] ?></strong></div>
        </div>
    </div>

    <hr>

    <!-- Billed To -->
    <div class="row my-4">
        <div class="col-6">
            <h6 class="fw-bold text-uppercase fs-8 text-muted mb-2">Billed To (Customer):</h6>
            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($invoice['company_name']) ?></h5>
            <p class="text-muted small mb-0">
                <?= htmlspecialchars($invoice['billing_address']) ?><br>
                <?= htmlspecialchars($invoice['city']) ?>, <?= htmlspecialchars($invoice['state']) ?> - <?= htmlspecialchars($invoice['pincode']) ?><br>
                Attn: <?= htmlspecialchars($invoice['contact_person']) ?> (<?= $invoice['mobile'] ?>)<br>
                <strong>GSTIN: <?= htmlspecialchars($invoice['customer_gstin'] ?? 'Unregistered') ?></strong>
            </p>
        </div>
        <div class="col-6 text-end">
            <?php if (!empty($invoice['contract_number'])): ?>
                <div class="p-2 bg-light rounded text-start d-inline-block border small">
                    <strong>AMC Contract:</strong> <?= $invoice['contract_number'] ?><br>
                    <strong>Agreement:</strong> <?= htmlspecialchars($invoice['contract_title']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table -->
    <table class="table table-bordered align-middle mb-4">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Description of Goods / Services</th>
                <th>HSN/SAC</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Unit Rate (₹)</th>
                <th class="text-end">CGST (₹)</th>
                <th class="text-end">SGST (₹)</th>
                <th class="text-end">Total Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($invoice['items'] as $item): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong class="text-dark"><?= htmlspecialchars($item['description']) ?></strong></td>
                    <td><code><?= $item['hsn_sac'] ?></code></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-end"><?= number_format($item['unit_price'], 2) ?></td>
                    <td class="text-end"><?= number_format($item['cgst'], 2) ?></td>
                    <td class="text-end"><?= number_format($item['sgst'], 2) ?></td>
                    <td class="text-end fw-bold"><?= number_format($item['total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-end fw-bold">Taxable Amount:</td>
                <td class="text-end fw-bold">₹<?= number_format($invoice['sub_total'], 2) ?></td>
            </tr>
            <tr>
                <td colspan="7" class="text-end fw-bold">Central GST (CGST 9%):</td>
                <td class="text-end text-muted">₹<?= number_format($invoice['cgst_amount'], 2) ?></td>
            </tr>
            <tr>
                <td colspan="7" class="text-end fw-bold">State GST (SGST 9%):</td>
                <td class="text-end text-muted">₹<?= number_format($invoice['sgst_amount'], 2) ?></td>
            </tr>
            <tr class="table-light">
                <td colspan="7" class="text-end fw-bold fs-6 text-primary">Total Invoice Value (INR):</td>
                <td class="text-end fw-bold fs-6 text-primary">₹<?= number_format($invoice['total_amount'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer & Bank Details -->
    <div class="row pt-3">
        <div class="col-7">
            <h6 class="fw-bold text-uppercase fs-8 text-muted mb-1">Bank Payment Details:</h6>
            <p class="small text-muted mb-0">
                Bank Name: <strong>HDFC Bank Ltd</strong><br>
                Account Name: <strong><?= htmlspecialchars($company['name']) ?></strong><br>
                Account No: <strong>50200012345678</strong> | IFSC Code: <strong>HDFC0000123</strong><br>
                UPI ID: <strong>apexit@hdfcbank</strong>
            </p>
        </div>
        <div class="col-5 text-end">
            <div class="text-xs text-muted mb-4">For <?= htmlspecialchars($company['name']) ?></div>
            <div class="fw-bold text-dark pt-4">Authorized Signatory</div>
        </div>
    </div>
</div>
</body>
</html>
