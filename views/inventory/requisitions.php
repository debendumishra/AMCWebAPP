<?php
/**
 * Spare Part Requisitions & Approval Queue View
 */
$totalReqs = count($requisitions);
$pendingCount = count(array_filter($requisitions, fn($r) => $r['status'] === 'REQUESTED'));
$approvedCount = count(array_filter($requisitions, fn($r) => $r['status'] === 'APPROVED'));
$rejectedCount = count(array_filter($requisitions, fn($r) => $r['status'] === 'REJECTED'));
$canManage = Auth::hasAnyRole([ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
?>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <span class="p-2 bg-warning-subtle text-warning rounded-3 fs-5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-box-seam"></i>
            </span>
            <span>Spare Part Requisitions & Approval Queue</span>
        </h4>
        <p class="text-muted small mb-0">Review field technician part requests, verify stock levels & AMC coverage, and approve inventory issues.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i> Inventory Catalog
        </a>
        <a href="<?= BASE_URL ?>/inventory/ledger" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-journal-text me-1"></i> Stock Ledger
        </a>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-muted mb-1">Total Requests</div>
                    <h3 class="fw-bold mb-0 text-dark"><?= $totalReqs ?></h3>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-warning mb-1">Pending Review</div>
                    <h3 class="fw-bold mb-0 text-warning"><?= $pendingCount ?></h3>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                    <i class="bi bi-hourglass-split fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-success mb-1">Approved & Issued</div>
                    <h3 class="fw-bold mb-0 text-success"><?= $approvedCount ?></h3>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-circle">
                    <i class="bi bi-check2-circle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-xs text-uppercase fw-bold text-danger mb-1">Rejected</div>
                    <h3 class="fw-bold mb-0 text-danger"><?= $rejectedCount ?></h3>
                </div>
                <div class="p-3 bg-danger-subtle text-danger rounded-circle">
                    <i class="bi bi-x-circle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Controls Bar (Filter Pills & Live Search) -->
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-12 col-md-auto">
            <div class="d-flex flex-wrap gap-2" id="filterPills">
                <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold btn-primary filter-btn active" data-filter="ALL">
                    All (<?= $totalReqs ?>)
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold btn-light border filter-btn" data-filter="REQUESTED">
                    <i class="bi bi-hourglass me-1 text-warning"></i> Pending (<?= $pendingCount ?>)
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold btn-light border filter-btn" data-filter="APPROVED">
                    <i class="bi bi-check-circle me-1 text-success"></i> Issued (<?= $approvedCount ?>)
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold btn-light border filter-btn" data-filter="REJECTED">
                    <i class="bi bi-x-circle me-1 text-danger"></i> Rejected (<?= $rejectedCount ?>)
                </button>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="requisitionSearch" class="form-control border-start-0" placeholder="Search by Req #, Ticket #, Customer, Part...">
            </div>
        </div>
    </div>
</div>

<!-- Requisitions List Card -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="requisitionsTable">
            <thead class="table-light">
                <tr>
                    <th style="width: 14%;" class="ps-3">Requisition #</th>
                    <th style="width: 18%;">Ticket / Customer</th>
                    <th style="width: 16%;">Technician</th>
                    <th style="width: 28%;">Product Requested & Stock Position</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 14%;" class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requisitions)): ?>
                    <tr id="noDataRow">
                        <td colspan="6" class="text-center text-muted py-5">
                            <div class="p-4">
                                <i class="bi bi-inbox display-4 d-block mb-3 text-secondary opacity-50"></i>
                                <h6 class="fw-bold text-dark">No Spare Requisitions Found</h6>
                                <p class="small text-muted mb-0">Field technician spare part requests will appear here for review and warehouse issue.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requisitions as $r): ?>
                        <tr class="requisition-row" data-status="<?= htmlspecialchars($r['status']) ?>" data-search="<?= strtolower(htmlspecialchars($r['request_number'] . ' ' . $r['call_number'] . ' ' . $r['company_name'] . ' ' . $r['engineer_name'] . ' ' . ($r['asset_code'] ?? ''))) ?>">
                            <!-- Requisition & Date -->
                            <td class="ps-3 align-top py-3">
                                <strong class="text-primary font-monospace fs-7 d-block"><?= htmlspecialchars($r['request_number']) ?></strong>
                                <div class="text-xs text-muted mt-1"><i class="bi bi-clock me-1"></i><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></div>
                                <?php if (!empty($r['contract_number'])): ?>
                                    <div class="text-xs mt-2">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-file-earmark-check me-1 text-primary"></i><?= htmlspecialchars($r['contract_type_name'] ?? 'AMC') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Ticket & Customer -->
                            <td class="align-top py-3">
                                <div class="fw-bold">
                                    <a href="<?= BASE_URL ?>/calls/<?= (int)$r['call_id'] ?>" class="text-decoration-none text-primary">
                                        <i class="bi bi-ticket-detailed me-1"></i><?= htmlspecialchars($r['call_number']) ?>
                                    </a>
                                </div>
                                <div class="small fw-semibold text-dark mt-1"><?= htmlspecialchars($r['company_name']) ?></div>
                                <?php if (!empty($r['asset_code'])): ?>
                                    <div class="text-xs text-muted mt-1">
                                        <i class="bi bi-pc-display me-1"></i><?= htmlspecialchars(($r['make'] ?? '') . ' ' . ($r['model'] ?? '')) ?>
                                        <code>(<?= htmlspecialchars($r['asset_code']) ?>)</code>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Field Technician -->
                            <td class="align-top py-3">
                                <div class="fw-semibold text-dark"><i class="bi bi-person-badge me-1 text-primary"></i><?= htmlspecialchars($r['engineer_name']) ?></div>
                                <?php if (!empty($r['engineer_mobile'])): ?>
                                    <div class="text-xs text-muted mt-1"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($r['engineer_mobile']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($r['approval_remarks'])): ?>
                                    <div class="p-2 bg-light rounded-3 text-xs text-muted mt-2 border">
                                        <strong>Notes:</strong> <?= htmlspecialchars($r['approval_remarks']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Product Requested & Stock Position -->
                            <td class="align-top py-3">
                                <?php if (empty($r['items'])): ?>
                                    <span class="text-muted small">No items attached</span>
                                <?php else: ?>
                                    <?php foreach ($r['items'] as $item): 
                                        $avail = (int)($item['available_stock'] ?? 0);
                                        $reqQty = (int)($item['requested_qty'] ?? 1);
                                        $hasStock = $avail >= $reqQty;
                                        $isLow = $avail > 0 && $avail < $reqQty;
                                    ?>
                                        <div class="p-2 rounded-3 border bg-light mb-2">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div>
                                                    <span class="fw-bold text-dark fs-7"><?= htmlspecialchars($item['spare_name']) ?></span>
                                                    <?php if (!empty($item['capacity_spec'])): ?>
                                                        <span class="badge bg-secondary-subtle text-secondary-emphasis text-xs ms-1"><?= htmlspecialchars($item['capacity_spec']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="badge bg-dark text-white font-monospace">Req: <?= $reqQty ?> PCS</span>
                                            </div>

                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 text-xs text-muted mt-1">
                                                <div>
                                                    <span class="text-muted">SKU: <code><?= htmlspecialchars($item['sku']) ?></code></span>
                                                    <span class="mx-1">•</span>
                                                    <span class="text-muted"><?= htmlspecialchars($item['category_name'] ?? 'General') ?></span>
                                                </div>
                                                
                                                <!-- AMC vs Chargeable -->
                                                <div>
                                                    <?php if (!empty($item['is_chargeable'])): ?>
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">
                                                            Chargeable (<?= defined('DEFAULT_CURRENCY') ? DEFAULT_CURRENCY : '₹' ?><?= number_format((float)($item['selling_price'] ?? 0), 2) ?>)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info-subtle text-info-emphasis border border-info">
                                                            Covered under AMC
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Live Stock Position Indicator -->
                                            <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between">
                                                <span class="text-xs fw-bold text-secondary">Warehouse Stock:</span>
                                                <?php if ($hasStock): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i> In Stock: <strong><?= $avail ?></strong> available
                                                    </span>
                                                <?php elseif ($isLow): ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">
                                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock: Only <strong><?= $avail ?></strong> (Need <?= $reqQty ?>)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                                        <i class="bi bi-x-circle-fill me-1"></i> Out of Stock (0 available)
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>

                            <!-- Status -->
                            <td class="align-top py-3">
                                <?php if ($r['status'] === 'APPROVED'): ?>
                                    <span class="badge bg-success px-2 py-1 fs-8"><i class="bi bi-check2-circle me-1"></i>Issued</span>
                                <?php elseif ($r['status'] === 'REQUESTED'): ?>
                                    <span class="badge bg-warning text-dark px-2 py-1 fs-8"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                                <?php elseif ($r['status'] === 'REJECTED'): ?>
                                    <span class="badge bg-danger px-2 py-1 fs-8"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary px-2 py-1 fs-8"><?= htmlspecialchars($r['status']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Action -->
                            <td class="text-end align-top py-3 pe-3">
                                <?php if ($r['status'] === 'REQUESTED' && $canManage): ?>
                                    <div class="d-flex flex-column gap-2 align-items-end">
                                        <form method="POST" action="<?= BASE_URL ?>/inventory/requisitions/<?= (int)$r['id'] ?>/approve" class="w-100">
                                            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                                            <input type="hidden" name="remarks" value="Approved by Operations Manager">
                                            <button type="submit" class="btn btn-sm btn-success w-100 fw-bold shadow-sm rounded-pill" onclick="return confirm('Approve spare issue and deduct warehouse inventory for Requisition <?= htmlspecialchars($r['request_number']) ?>?')">
                                                <i class="bi bi-check-lg me-1"></i> Approve & Issue
                                            </button>
                                        </form>

                                        <form method="POST" action="<?= BASE_URL ?>/inventory/requisitions/<?= (int)$r['id'] ?>/reject" class="w-100">
                                            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                                            <input type="hidden" name="remarks" value="Rejected: Part not available or not covered">
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100 rounded-pill" onclick="return confirm('Reject Requisition <?= htmlspecialchars($r['request_number']) ?>?')">
                                                <i class="bi bi-x-lg me-1"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="text-xs text-muted">
                                        <i class="bi bi-shield-check me-1 text-success"></i>Completed
                                    </div>
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
document.addEventListener('DOMContentLoaded', function() {
    var filterBtns = document.querySelectorAll('.filter-btn');
    var searchInput = document.getElementById('requisitionSearch');
    var rows = document.querySelectorAll('.requisition-row');
    var currentFilter = 'ALL';

    function applyFilters() {
        var query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        rows.forEach(function(row) {
            var status = row.getAttribute('data-status') || '';
            var searchData = row.getAttribute('data-search') || '';
            var text = row.innerText.toLowerCase();

            var matchesFilter = (currentFilter === 'ALL' || status === currentFilter);
            var matchesSearch = !query || searchData.includes(query) || text.includes(query);

            if (matchesFilter && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(function(b) {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-light', 'border');
            });
            this.classList.remove('btn-light', 'border');
            this.classList.add('btn-primary', 'active');

            currentFilter = this.getAttribute('data-filter') || 'ALL';
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
});
</script>
