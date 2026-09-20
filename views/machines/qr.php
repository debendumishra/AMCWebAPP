<?php
/**
 * Mobile-Optimized QR Asset Scan Landing View
 */
?>
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-3">
    <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-qr-code-scan text-info fs-4"></i>
            <div>
                <h6 class="fw-bold mb-0 text-white"><?= htmlspecialchars($machine['make'] . ' ' . $machine['model']) ?></h6>
                <span class="text-xs text-info">Asset Code: <?= $machine['asset_code'] ?></span>
            </div>
        </div>
        <span class="badge <?= $machine['status'] === 'UNDER_AMC' ? 'bg-success' : 'bg-secondary' ?>">
            <?= $machine['status'] ?>
        </span>
    </div>
    <div class="card-body p-3">
        <!-- Client & Placement Summary -->
        <div class="p-3 bg-light rounded-3 mb-3">
            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($machine['company_name']) ?></div>
            <div class="small text-muted mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($machine['location_name']) ?> (<?= htmlspecialchars($machine['location_address']) ?>)</div>
            <div class="small text-muted"><i class="bi bi-person-workspace me-1"></i>Dept: <strong><?= htmlspecialchars($machine['department'] ?? 'General') ?></strong> | User: <strong><?= htmlspecialchars($machine['assigned_employee'] ?? 'Shared') ?></strong></div>
        </div>

        <!-- Technical Specs -->
        <div class="row g-2 small mb-3">
            <div class="col-6">
                <span class="text-muted d-block">Serial Number:</span>
                <strong><code><?= htmlspecialchars($machine['serial_number']) ?></code></strong>
            </div>
            <div class="col-6">
                <span class="text-muted d-block">IP Address:</span>
                <strong><?= htmlspecialchars($machine['ip_address'] ?? 'DHCP') ?></strong>
            </div>
            <div class="col-6">
                <span class="text-muted d-block">CPU / Processor:</span>
                <strong><?= htmlspecialchars($machine['processor'] ?? 'Standard') ?></strong>
            </div>
            <div class="col-6">
                <span class="text-muted d-block">RAM & Storage:</span>
                <strong><?= htmlspecialchars($machine['ram'] ?? '8GB') ?> / <?= htmlspecialchars($machine['storage'] ?? 'SSD') ?></strong>
            </div>
        </div>

        <!-- AMC Coverage Status -->
        <?php if (!empty($machine['contract_number'])): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 p-2 mb-3">
                <i class="bi bi-shield-fill-check fs-4"></i>
                <div class="small">
                    <strong>AMC Active (<?= $machine['contract_number'] ?>)</strong><br>
                    Labour: Covered | Spares: <?= $machine['is_spares_covered'] ? 'Included' : 'Chargeable' ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning small p-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Non-AMC Asset (Standard Chargeable Rates Apply)
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <?php
        $createCallUrl = BASE_URL . '/calls/create';
        if (Auth::hasRole(ROLE_ENGINEER)) {
            $createCallUrl = BASE_URL . '/engineer/calls/create';
        } elseif (Auth::hasRole(ROLE_CUSTOMER)) {
            $createCallUrl = BASE_URL . '/customer/calls/create';
        }
        ?>
        <div class="d-grid gap-2">
            <a href="<?= $createCallUrl ?>?machine_id=<?= $machine['id'] ?>&customer_id=<?= $machine['customer_id'] ?>" class="btn btn-primary btn-touch-action">
                <i class="bi bi-plus-circle-fill"></i> Raise Service Ticket for Machine
            </a>
            <a href="<?= BASE_URL ?>/machines/<?= $machine['id'] ?>" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history me-1"></i> View Full Service History (<?= count($serviceHistory) ?>)
            </a>
        </div>
    </div>
</div>
