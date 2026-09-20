<?php
/**
 * Invoice Detail & Payment Receipts View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0"><?= $invoice['invoice_number'] ?></h4>
            <span class="badge bg-light text-dark border"><?= $invoice['invoice_type'] ?></span>
            <span class="badge <?= $invoice['status'] === 'PAID' ? 'bg-success' : ($invoice['status'] === 'PARTIALLY_PAID' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                <?= str_replace('_', ' ', $invoice['status']) ?>
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <i class="bi bi-building"></i> <?= htmlspecialchars($invoice['company_name']) ?> | Date: <strong><?= $invoice['invoice_date'] ?></strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/billing/<?= $invoice['id'] ?>/print" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-printer me-1"></i> Print / PDF Invoice
        </a>
        <?php if ($invoice['balance_amount'] > 0): ?>
            <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                <i class="bi bi-cash-stack me-1"></i> Record Payment Received
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Invoice Financial Summary -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-list-check text-primary me-2"></i>Invoice Line Items</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>HSN/SAC</th>
                            <th>Qty</th>
                            <th>Unit Rate</th>
                            <th>CGST</th>
                            <th>SGST</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoice['items'] as $item): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($item['description']) ?></td>
                                <td><code><?= $item['hsn_sac'] ?></code></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>₹<?= number_format($item['unit_price'], 2) ?></td>
                                <td>₹<?= number_format($item['cgst'], 2) ?></td>
                                <td>₹<?= number_format($item['sgst'], 2) ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($item['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end fw-bold">₹<?= number_format($invoice['sub_total'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total CGST (9%):</td>
                            <td class="text-end text-muted">₹<?= number_format($invoice['cgst_amount'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total SGST (9%):</td>
                            <td class="text-end text-muted">₹<?= number_format($invoice['sgst_amount'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-end fw-bold fs-6 text-primary">Invoice Total (INR):</td>
                            <td class="text-end fw-bold fs-6 text-primary">₹<?= number_format($invoice['total_amount'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment & Balance Breakdown -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>Payment Breakdown</span>
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total Billed:</span>
                        <strong class="text-dark">₹<?= number_format($invoice['total_amount'], 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Amount Received:</span>
                        <strong class="text-success">₹<?= number_format($invoice['paid_amount'], 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="fw-bold small">Balance Due:</span>
                        <strong class="text-danger fs-5">₹<?= number_format($invoice['balance_amount'], 2) ?></strong>
                    </div>
                </div>

                <h6 class="fw-bold text-uppercase fs-8 text-muted mb-2">Payment Receipts (<?= count($invoice['payments']) ?>)</h6>
                <?php if (empty($invoice['payments'])): ?>
                    <div class="text-muted small">No payment receipts recorded yet.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush border rounded small">
                        <?php foreach ($invoice['payments'] as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>₹<?= number_format($p['amount'], 2) ?></strong> (<?= $p['payment_mode'] ?>)
                                    <div class="text-xs text-muted"><?= $p['payment_date'] ?> | Ref: <?= $p['transaction_reference'] ?? 'N/A' ?></div>
                                </div>
                                <span class="badge bg-success">Received</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/billing/<?= $invoice['id'] ?>/payment">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>Record Payment Received</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Amount Received (INR) *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" name="amount" class="form-control" value="<?= $invoice['balance_amount'] ?>" max="<?= $invoice['balance_amount'] ?>" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Payment Mode *</label>
                            <select name="payment_mode" class="form-select">
                                <option value="UPI">UPI / QR Payment</option>
                                <option value="BANK_TRANSFER">Bank NEFT / RTGS</option>
                                <option value="CHEQUE">Cheque</option>
                                <option value="CASH">Cash</option>
                                <option value="CARD">Credit / Debit Card</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Transaction Ref / UTR / Cheque No</label>
                            <input type="text" name="transaction_reference" class="form-control" placeholder="UPI Ref / UTR">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" placeholder="e.g. HDFC Bank">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional payment receipt note">
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Save Payment Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>
