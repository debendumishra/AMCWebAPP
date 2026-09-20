<?php
/**
 * Immutable Stock Ledger View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Double-Entry Stock Ledger (Audit Log)</h4>
        <p class="text-muted small mb-0">Immutable transaction history of all stock openings, purchases, field issues, and adjustments.</p>
    </div>
    <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Inventory Catalog
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Spare Part & SKU</th>
                    <th>Transaction Type</th>
                    <th>Reference</th>
                    <th>Qty In (+)</th>
                    <th>Qty Out (-)</th>
                    <th>Balance After</th>
                    <th>User / Performed By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ledgerEntries)): ?>
                    <tr><td colspan="8" class="text-center text-muted p-4">No inventory transactions logged yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($ledgerEntries as $tx): ?>
                        <tr>
                            <td><span class="small text-muted"><?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($tx['spare_name']) ?></div>
                                <div class="text-xs text-muted"><code><?= htmlspecialchars($tx['sku']) ?></code></div>
                            </td>
                            <td>
                                <span class="badge <?= $tx['transaction_type'] === 'PURCHASE' || $tx['transaction_type'] === 'OPENING' ? 'bg-success' : ($tx['transaction_type'] === 'ISSUE' ? 'bg-danger' : 'bg-info') ?>">
                                    <?= $tx['transaction_type'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= $tx['reference_type'] ?></div>
                                <div class="text-xs text-muted"><?= $tx['reference_id'] ?? '-' ?></div>
                            </td>
                            <td>
                                <?php if ($tx['quantity_in'] > 0): ?>
                                    <span class="text-success fw-bold">+<?= $tx['quantity_in'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($tx['quantity_out'] > 0): ?>
                                    <span class="text-danger fw-bold">-<?= $tx['quantity_out'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><strong class="text-primary fs-6"><?= $tx['balance_after'] ?></strong> <?= $tx['unit'] ?></td>
                            <td><span class="small text-muted"><?= htmlspecialchars($tx['user_name'] ?? 'System') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
