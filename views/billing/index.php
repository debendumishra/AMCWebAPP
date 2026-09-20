<?php
/**
 * Invoicing & Billing Register View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Invoices, GST Billing & Receipts</h4>
        <p class="text-muted small mb-0">Manage customer AMC invoices, chargeable service calls, GST tax registers, and payment collections.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/billing/create" class="btn btn-primary shadow-sm btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Generate Tax Invoice
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Customer Company</th>
                    <th>Invoice Type</th>
                    <th>Date & Due Date</th>
                    <th>Total (INR)</th>
                    <th>Paid Amount</th>
                    <th>Outstanding Balance</th>
                    <th>Payment Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="9" class="text-center text-muted p-4">No invoices generated yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/billing/<?= $inv['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= $inv['invoice_number'] ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($inv['company_name']) ?></div>
                                <div class="text-xs text-muted"><?= $inv['customer_code'] ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= $inv['invoice_type'] ?></span></td>
                            <td>
                                <div class="small fw-semibold"><?= $inv['invoice_date'] ?></div>
                                <div class="text-xs text-muted">Due: <?= $inv['due_date'] ?></div>
                            </td>
                            <td class="fw-bold text-dark">₹<?= number_format($inv['total_amount'], 2) ?></td>
                            <td class="text-success fw-semibold">₹<?= number_format($inv['paid_amount'], 2) ?></td>
                            <td>
                                <?php if ($inv['balance_amount'] > 0): ?>
                                    <span class="text-danger fw-bold">₹<?= number_format($inv['balance_amount'], 2) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">₹0.00</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $inv['status'] === 'PAID' ? 'bg-success' : ($inv['status'] === 'PARTIALLY_PAID' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                    <?= str_replace('_', ' ', $inv['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/billing/<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Invoice">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/billing/<?= $inv['id'] ?>/print" target="_blank" class="btn btn-sm btn-outline-dark ms-1" title="Print Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
