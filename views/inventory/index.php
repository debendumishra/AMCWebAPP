<?php
/**
 * Spare Parts Master & Stock Levels View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Spare Parts & Warehouse Inventory</h4>
        <p class="text-muted small mb-0">Manage hardware spare catalog, real-time stock levels, purchase/sale pricing, and reorder triggers.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/inventory/ledger" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-journal-text me-1"></i> Stock Ledger
        </a>
        <a href="<?= BASE_URL ?>/inventory/create" class="btn btn-primary shadow-sm btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Spare Master
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="fw-bold text-dark"><i class="bi bi-boxes text-primary me-2"></i>Spare Parts Inventory (<?= count($spares) ?> Items)</span>
            </div>
            <div class="col-md-6 text-md-end">
                <input type="text" id="filterSpares" class="form-control form-control-sm d-inline-block w-auto" placeholder="Filter by part, SKU, brand...">
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="sparesTable">
            <thead>
                <tr>
                    <th>SKU / Part Name</th>
                    <th>Category & Brand</th>
                    <th>Capacity / Spec</th>
                    <th>Purchase Price</th>
                    <th>Selling Price</th>
                    <th>Current Stock</th>
                    <th>Stock Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($spares)): ?>
                    <tr><td colspan="7" class="text-center text-muted p-4">No spare parts defined in catalog.</td></tr>
                <?php else: ?>
                    <?php foreach ($spares as $sp): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($sp['name']) ?></div>
                                <div class="text-xs text-muted"><code><?= htmlspecialchars($sp['sku']) ?></code> | HSN: <?= $sp['hsn_code'] ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($sp['category_name']) ?></div>
                                <div class="text-xs text-muted"><?= htmlspecialchars($sp['brand_name'] ?? 'Generic') ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($sp['capacity_spec'] ?? 'N/A') ?></span></td>
                            <td class="text-muted">₹<?= number_format($sp['purchase_price'], 2) ?></td>
                            <td class="fw-bold text-dark">₹<?= number_format($sp['selling_price'], 2) ?></td>
                            <td>
                                <span class="fw-bold fs-6 <?= ($sp['current_stock'] <= $sp['min_stock_level']) ? 'text-danger' : 'text-success' ?>">
                                    <?= $sp['current_stock'] ?> <?= $sp['unit'] ?>
                                </span>
                                <div class="text-xs text-muted">Min: <?= $sp['min_stock_level'] ?></div>
                            </td>
                            <td>
                                <?php if ($sp['current_stock'] <= $sp['min_stock_level']): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-exclamation-triangle-fill me-1"></i>Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('filterSpares')?.addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#sparesTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
