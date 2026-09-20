<?php
/**
 * Purchase Register & Inward Invoices View
 */
$totalCount = count($purchases);
$totalSpend = array_sum(array_column($purchases, 'total_amount'));
$totalTax = array_sum(array_column($purchases, 'tax_amount'));
$uniqueVendors = count(array_unique(array_column($purchases, 'supplier_id')));
?>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <span class="p-2 bg-primary-subtle text-primary rounded-3 fs-5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-cart-check"></i>
            </span>
            <span>Purchases & Inward Stock Register</span>
        </h4>
        <p class="text-muted small mb-0">Record vendor hardware invoices, manage input tax credit (GST), and audit inventory inflows.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/purchases/suppliers" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-truck me-1"></i> Suppliers Directory
        </a>
        <a href="<?= BASE_URL ?>/purchases/create" class="btn btn-primary shadow-sm btn-sm rounded-pill px-3 fw-bold">
            <i class="bi bi-plus-circle me-1"></i> New Purchase Entry
        </a>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-muted mb-1">Total Purchases</div>
                    <h3 class="fw-bold mb-0 text-dark"><?= $totalCount ?></h3>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                    <i class="bi bi-receipt-cutoff fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-success mb-1">Total Inward Value</div>
                    <h3 class="fw-bold mb-0 text-success">₹<?= number_format($totalSpend, 2) ?></h3>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-circle">
                    <i class="bi bi-currency-rupee fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-info mb-1">GST Input Tax</div>
                    <h3 class="fw-bold mb-0 text-info">₹<?= number_format($totalTax, 2) ?></h3>
                </div>
                <div class="p-3 bg-info-subtle text-info rounded-circle">
                    <i class="bi bi-percent fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-warning mb-1">Active Vendors</div>
                    <h3 class="fw-bold mb-0 text-warning"><?= $uniqueVendors ?></h3>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                    <i class="bi bi-buildings fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Controls Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-12 col-md-6">
            <span class="fw-bold text-dark fs-7">
                <i class="bi bi-journal-check text-primary me-2"></i>Inward Purchase Entries (<?= $totalCount ?>)
            </span>
        </div>
        <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="purchaseSearch" class="form-control border-start-0" placeholder="Search by Purchase #, Supplier, Invoice #...">
            </div>
        </div>
    </div>
</div>

<!-- Purchase Register Table -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="purchasesTable">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Purchase Number</th>
                    <th>Supplier / Vendor</th>
                    <th>Vendor Invoice #</th>
                    <th>Date</th>
                    <th>Subtotal</th>
                    <th>GST Tax</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($purchases)): ?>
                    <tr id="noDataRow">
                        <td colspan="9" class="text-center text-muted py-5">
                            <div class="p-4">
                                <i class="bi bi-cart-x display-4 d-block mb-3 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">No Purchase Entries Recorded</h6>
                                <p class="small text-muted mb-0">Record hardware spare shipments and supplier invoices to maintain accurate inventory ledgers.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($purchases as $p): ?>
                        <tr class="purchase-row" data-search="<?= strtolower(htmlspecialchars($p['purchase_number'] . ' ' . $p['supplier_name'] . ' ' . $p['supplier_invoice_no'] . ' ' . ($p['supplier_mobile'] ?? ''))) ?>">
                            <td class="ps-3">
                                <a href="<?= BASE_URL ?>/purchases/<?= $p['id'] ?>" class="fw-bold text-primary font-monospace text-decoration-none">
                                    <?= htmlspecialchars($p['purchase_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($p['supplier_name']) ?></div>
                                <?php if (!empty($p['supplier_mobile'])): ?>
                                    <div class="text-xs text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($p['supplier_mobile']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    <?= htmlspecialchars($p['supplier_invoice_no']) ?>
                                </span>
                            </td>
                            <td><span class="small text-muted"><i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($p['purchase_date'])) ?></span></td>
                            <td class="text-muted">₹<?= number_format($p['sub_total'], 2) ?></td>
                            <td class="text-muted">₹<?= number_format($p['tax_amount'], 2) ?></td>
                            <td><strong class="text-dark fs-7">₹<?= number_format($p['total_amount'], 2) ?></strong></td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fs-8">
                                    <i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($p['payment_status'] ?? 'PAID') ?>
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="<?= BASE_URL ?>/purchases/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="View Purchase Inward">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('purchaseSearch');
    var rows = document.querySelectorAll('.purchase-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                var searchData = row.getAttribute('data-search') || '';
                var text = row.innerText.toLowerCase();
                row.style.display = (!q || searchData.includes(q) || text.includes(q)) ? '' : 'none';
            });
        });
    }
});
</script>
