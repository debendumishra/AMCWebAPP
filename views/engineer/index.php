<?php
/**
 * Field Engineer Today's Dashboard View
 */
$criticalCount = count(array_filter($activeCalls, fn($c) => in_array($c['priority'] ?? '', ['CRITICAL', 'HIGH'])));
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-calendar2-check text-primary"></i> Today's Assigned Schedule
        </h4>
        <div class="text-xs text-muted"><?= date('l, d F Y') ?> &bull; Field Service Queue</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= BASE_URL ?>/engineer/calls/create" class="btn btn-sm btn-primary rounded-pill px-3 py-2 shadow-sm fw-bold d-inline-flex align-items-center gap-1">
            <i class="bi bi-plus-circle-fill"></i> <span>Log Ticket</span>
        </a>
        <span class="badge bg-primary rounded-pill px-3 py-2 fs-7 shadow-sm"><?= count($activeCalls) ?> Active Calls</span>
    </div>
</div>

<!-- Quick Stats Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Active Tasks</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1"><?= count($activeCalls) ?></h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-tools fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">High Priority</div>
                    <h3 class="fw-bold text-danger mb-0 mt-1"><?= $criticalCount ?></h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Completed Today</div>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= count($completedCalls ?? []) ?></h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-check2-circle fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">QR Quick Scan</div>
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 mt-1 fw-bold text-nowrap" onclick="openQrScannerModal()">
                        <i class="bi bi-qr-code-scan text-info me-1"></i> Scan
                    </button>
                </div>
                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-camera fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Task Cards -->
<?php if (empty($activeCalls)): ?>
    <div class="card p-5 text-center border-0 shadow-sm rounded-4 my-4 bg-white">
        <i class="bi bi-emoji-sunglasses text-success display-3 mb-2"></i>
        <h5 class="fw-bold text-dark">All Tasks Completed!</h5>
        <p class="text-muted small mb-3">You have no pending breakdown or PM calls assigned right now.</p>
        <div>
            <a href="<?= BASE_URL ?>/engineer/calls/create" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Raise / Log New Complain
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 g-md-4">
        <?php foreach ($activeCalls as $call): ?>
            <div class="col-12 col-md-6 col-xxl-4 d-flex">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden w-100 d-flex flex-column bg-white">
                    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="badge bg-dark fw-bold text-nowrap"><?= $call['call_number'] ?></span>
                        <span class="badge <?= $call['priority'] === 'CRITICAL' ? 'bg-danger' : ($call['priority'] === 'HIGH' ? 'bg-warning text-dark' : 'bg-info-subtle text-info') ?>">
                            <?= $call['priority'] ?>
                        </span>
                    </div>
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($call['company_name']) ?></h6>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($call['location_name']) ?>
                        </div>

                        <?php if (!empty($call['asset_code'])): ?>
                            <div class="p-2 bg-light rounded-3 small mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-pc-display text-primary fs-5"></i>
                                <div>
                                    <strong><?= htmlspecialchars($call['make'] . ' ' . $call['model']) ?></strong>
                                    <div class="text-xs text-muted">S/N: <?= htmlspecialchars($call['serial_number']) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="small text-dark mb-3 flex-grow-1">
                            <strong>Complaint:</strong> <?= htmlspecialchars($call['reported_issue']) ?>
                        </div>

                        <!-- Action Button by Status -->
                        <div class="d-grid mt-auto pt-2">
                            <a href="<?= BASE_URL ?>/engineer/calls/<?= $call['id'] ?>" class="btn btn-primary btn-touch-action">
                                <?php if ($call['status'] === 'ASSIGNED'): ?>
                                    <i class="bi bi-check2-circle"></i> Accept & Start Journey
                                <?php elseif ($call['status'] === 'ON_THE_WAY'): ?>
                                    <i class="bi bi-geo-fill"></i> Check-in Arrival at Site
                                <?php elseif ($call['status'] === 'ARRIVED' || $call['status'] === 'DIAGNOSIS'): ?>
                                    <i class="bi bi-tools"></i> Open Job Card / Request Spare
                                <?php else: ?>
                                    <i class="bi bi-arrow-right"></i> Open Active Ticket
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-2 px-3 d-flex justify-content-between align-items-center text-muted small">
                        <span>Status: <strong class="text-primary"><?= str_replace('_', ' ', $call['status']) ?></strong></span>
                        <span><a href="tel:<?= $call['caller_mobile'] ?>" class="text-decoration-none text-muted"><i class="bi bi-telephone-fill text-success me-1"></i><?= $call['caller_mobile'] ?></a></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
