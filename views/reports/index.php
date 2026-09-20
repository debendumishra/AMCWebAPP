<?php
/**
 * Reports & Analytics Dashboard View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Operational Analytics & Reports</h4>
        <p class="text-muted small mb-0">Service level agreements, technician throughput, inventory valuation, and raw data export.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/reports/export?type=calls" class="btn btn-outline-success btn-sm fw-bold">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Calls to CSV
        </a>
    </div>
</div>

<!-- Key Financial & Operational Highlights -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <div class="stat-label">Warehouse Inventory Valuation</div>
                <div class="stat-val text-primary">₹<?= number_format($stockSummary['total_valuation'] ?? 0, 2) ?></div>
                <div class="text-xs text-muted mt-1"><?= $stockSummary['total_quantity'] ?? 0 ?> units across <?= $stockSummary['total_items'] ?? 0 ?> SKUs</div>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-cash-coin"></i>
            </div>
        </div>
    </div>
</div>

<!-- Engineer Operational Performance Table -->
<div class="card mb-4">
    <div class="card-header bg-white py-3">
        <span class="fw-bold text-dark"><i class="bi bi-person-workspace text-primary me-2"></i>Field Technician Operational Metrics</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Engineer Name</th>
                    <th>Mobile</th>
                    <th class="text-center">Assigned Jobs</th>
                    <th class="text-center">Resolved / Closed</th>
                    <th class="text-center">In-Progress</th>
                    <th class="text-center">SLA Breaches</th>
                    <th class="text-end">Success TAT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($engMetrics as $em): ?>
                    <?php 
                        $total = (int)$em['total_assigned'];
                        $done = (int)$em['completed_calls'];
                        $rate = $total > 0 ? round(($done / $total) * 100) : 100;
                    ?>
                    <tr>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($em['engineer_name']) ?></td>
                        <td><?= $em['mobile'] ?></td>
                        <td class="text-center fw-bold"><?= $em['total_assigned'] ?></td>
                        <td class="text-center text-success fw-bold"><?= $em['completed_calls'] ?></td>
                        <td class="text-center text-warning fw-bold"><?= $em['pending_calls'] ?></td>
                        <td class="text-center <?= $em['sla_breaches'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>"><?= $em['sla_breaches'] ?></td>
                        <td class="text-end">
                            <span class="badge bg-success-subtle text-success"><?= $rate ?>% Closed</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
