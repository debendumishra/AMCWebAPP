<?php
/**
 * Purchase Detail & Line Items View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0"><?= $purchase['purchase_number'] ?></h4>
            <span class="badge bg-dark">Vendor Inv: <?= htmlspecialchars($purchase['supplier_invoice_no']) ?></span>
            <span class="badge bg-success"><?= $purchase['payment_status'] ?></span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <i class="bi bi-truck"></i> <?= htmlspecialchars($purchase['supplier_name']) ?> | Date: <strong><?= $purchase['purchase_date'] ?></strong>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/purchases" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Register
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-white py-3">
        <span class="fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Inward Items</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>SKU</th>
                    <th>Inward Qty</th>
                    <th>Rate (INR)</th>
                    <th>GST (%)</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($purchase['items'] as $item): ?>
                    <tr>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($item['spare_name']) ?></td>
                        <td><code><?= htmlspecialchars($item['sku']) ?></code></td>
                        <td><strong><?= $item['quantity'] ?></strong> <?= $item['unit'] ?></td>
                        <td>₹<?= number_format($item['rate'], 2) ?></td>
                        <td><?= $item['gst_percent'] ?>%</td>
                        <td class="text-end fw-bold text-dark">₹<?= number_format($item['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="5" class="text-end fw-bold">Taxable Subtotal:</td>
                    <td class="text-end fw-bold">₹<?= number_format($purchase['sub_total'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Tax Amount (GST):</td>
                    <td class="text-end fw-bold text-muted">₹<?= number_format($purchase['tax_amount'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold fs-6 text-primary">Grand Total:</td>
                    <td class="text-end fw-bold fs-6 text-primary">₹<?= number_format($purchase['total_amount'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
