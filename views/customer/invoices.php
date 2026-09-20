<?php
/**
 * Customer Invoices & Billing View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">My AMC & Service Invoices</h4>
        <p class="text-muted small mb-0">View statements, download tax invoices, and verify payment status.</p>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Billed Amount</th>
                    <th>Paid</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="8" class="text-center text-muted p-4">No invoices on record.</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><strong class="text-primary"><?= $inv['invoice_number'] ?></strong></td>
                            <td><?= $inv['invoice_date'] ?></td>
                            <td><?= $inv['due_date'] ?></td>
                            <td class="fw-bold text-dark">₹<?= number_format($inv['total_amount'], 2) ?></td>
                            <td class="text-success">₹<?= number_format($inv['paid_amount'], 2) ?></td>
                            <td>
                                <?php if ($inv['balance_amount'] > 0): ?>
                                    <span class="text-danger fw-bold">₹<?= number_format($inv['balance_amount'], 2) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">₹0.00</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $inv['status'] === 'PAID' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= $inv['status'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/billing/<?= $inv['id'] ?>/print" target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-printer me-1"></i> View Tax Invoice
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
