<?php
/**
 * Machine Asset Detail & Lifecycle View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0"><?= htmlspecialchars($machine['make'] . ' ' . $machine['model']) ?></h4>
            <span class="badge bg-dark"><?= $machine['asset_code'] ?></span>
            <?php if ($machine['status'] === 'UNDER_AMC'): ?>
                <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>UNDER AMC</span>
            <?php else: ?>
                <span class="badge bg-secondary"><?= $machine['status'] ?></span>
            <?php endif; ?>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <i class="bi bi-building"></i> <?= htmlspecialchars($machine['company_name']) ?> (<?= htmlspecialchars($machine['location_name']) ?>) |
            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($machine['department'] ?? 'General') ?> |
            <i class="bi bi-person"></i> <?= htmlspecialchars($machine['assigned_employee'] ?? 'Shared') ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/calls/create?machine_id=<?= $machine['id'] ?>&customer_id=<?= $machine['customer_id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Log Service Call
        </a>
        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Sticker
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Hardware Specifications Card -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-cpu text-primary me-2"></i>Technical Hardware Specifications</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Serial Number</label>
                        <div class="fw-bold fs-6"><code><?= htmlspecialchars($machine['serial_number']) ?></code></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Asset Tag / Identifier</label>
                        <div class="fw-bold fs-6"><?= htmlspecialchars($machine['asset_tag'] ?? 'N/A') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Processor / CPU</label>
                        <div class="fw-semibold"><?= htmlspecialchars($machine['processor'] ?? 'Standard CPU') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Installed Memory (RAM)</label>
                        <div class="fw-semibold"><?= htmlspecialchars($machine['ram'] ?? '8 GB') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Primary Storage (SSD/HDD)</label>
                        <div class="fw-semibold"><?= htmlspecialchars($machine['storage'] ?? '512 GB SSD') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Operating System</label>
                        <div class="fw-semibold"><?= htmlspecialchars($machine['operating_system'] ?? 'Windows / Linux') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Network IP Address</label>
                        <div><code><?= htmlspecialchars($machine['ip_address'] ?? 'DHCP Assigned') ?></code></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">MAC Physical Address</label>
                        <div><code><?= htmlspecialchars($machine['mac_address'] ?? 'N/A') ?></code></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AMC & QR Tag Sticker Card -->
    <div class="col-lg-4">
        <!-- Contract Coverage Box -->
        <div class="card mb-3">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-shield-check text-success me-2"></i>AMC Contract Coverage</span>
            </div>
            <div class="card-body">
                <?php if (!empty($machine['contract_number'])): ?>
                    <div class="fw-bold text-primary mb-1"><?= htmlspecialchars($machine['contract_title']) ?></div>
                    <div class="small text-muted mb-2">Contract No: <strong><?= $machine['contract_number'] ?></strong></div>
                    <div class="p-2 bg-light rounded small mb-2">
                        <div><i class="bi bi-check-circle-fill text-success me-1"></i> Labour & Visits: <strong>Included</strong></div>
                        <div>
                            <i class="bi <?= $machine['is_spares_covered'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?> me-1"></i> 
                            Spare Parts: <strong><?= $machine['is_spares_covered'] ? 'Covered (Zero Cost)' : 'Chargeable Extra' ?></strong>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i> Machine is not currently mapped to an active AMC contract. Service calls are chargeable.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Asset QR Sticker Widget -->
        <div class="card text-center p-3 border-2 border-primary border-opacity-25">
            <div class="fw-bold text-dark mb-1">Asset QR Sticker</div>
            <div class="text-xs text-muted mb-3">Scan with Mobile App for Sub-second Field Access</div>
            <div class="bg-white p-3 d-inline-block rounded-3 shadow-sm border mx-auto mb-2">
                <!-- High-res QR code image rendered dynamically via SVG API -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode(BASE_URL . '/machines/qr/' . $machine['qr_code_token']) ?>" alt="Asset QR Tag" width="140" height="140">
            </div>
            <div class="fw-bold text-primary small"><code><?= $machine['asset_code'] ?></code></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($machine['serial_number']) ?></div>
        </div>
    </div>
</div>

<!-- Machine Service & Repair History Timeline -->
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Service & Maintenance History</span>
        <span class="badge bg-light text-dark border"><?= count($serviceHistory) ?> Service Records</span>
    </div>
    <div class="card-body">
        <?php if (empty($serviceHistory)): ?>
            <div class="text-center text-muted p-4">No past service calls recorded for this asset. Machine running smoothly.</div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($serviceHistory as $sh): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker <?= $sh['status'] === 'CLOSED' ? 'success' : ($sh['priority'] === 'CRITICAL' ? 'danger' : 'warning') ?>"></div>
                        <div class="timeline-title d-flex justify-content-between">
                            <span>
                                <a href="<?= BASE_URL ?>/calls/<?= $sh['id'] ?>" class="text-primary text-decoration-none">
                                    <?= $sh['call_number'] ?>
                                </a> - <?= htmlspecialchars($sh['reported_issue']) ?>
                            </span>
                            <span class="badge-status badge-status-<?= strtolower($sh['status']) ?>">
                                <?= $sh['status'] ?>
                            </span>
                        </div>
                        <div class="timeline-time">
                            <i class="bi bi-calendar"></i> Logged: <?= $sh['created_at'] ?> |
                            <i class="bi bi-person"></i> Technician: <?= htmlspecialchars($sh['engineer_name'] ?? 'Unassigned') ?>
                            <?php if (!empty($sh['report_number'])): ?>
                                | <i class="bi bi-file-earmark-pdf"></i> Service Report: <strong><?= $sh['report_number'] ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
