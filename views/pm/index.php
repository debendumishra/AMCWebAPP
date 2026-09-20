<?php
/**
 * Preventive Maintenance (PM) Schedule Overview & Automation Hub
 */
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-calendar-check-fill text-primary"></i> Preventive Maintenance (PM) Engine
        </h4>
        <p class="text-muted small mb-0">Automated recurring health checks, cleaning checklists, and PM ticket dispatch hub.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="POST" action="<?= BASE_URL ?>/pm/sync-schedules" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
            <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-bold" title="Sync recurring PM visit schedules for all active AMC contracts">
                <i class="bi bi-arrow-repeat me-1"></i> Sync PM Schedules
            </button>
        </form>

        <a href="<?= BASE_URL ?>/pm/checklists" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
            <i class="bi bi-card-checklist me-1 text-primary"></i> PM Checklists
        </a>

        <form method="POST" action="<?= BASE_URL ?>/pm/generate-calls" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold" <?= $dueCount === 0 ? 'disabled' : '' ?>>
                <i class="bi bi-gear-wide-connected me-1"></i> Generate Due PM Calls (<?= $dueCount ?>)
            </button>
        </form>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Total Scheduled</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1"><?= $totalCount ?></h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-calendar-event fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Due / Overdue</div>
                    <h3 class="fw-bold <?= $dueCount > 0 ? 'text-danger' : 'text-success' ?> mb-0 mt-1"><?= $dueCount ?></h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">In Dispatch Queue</div>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= $generatedCount ?></h3>
                </div>
                <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-ticket-detailed fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-xs text-muted text-uppercase fw-bold">Completed PMs</div>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= $completedCount ?></h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="bi bi-check2-all fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/pm" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-1 overflow-x-auto pb-1 no-scrollbar">
                    <a href="<?= BASE_URL ?>/pm" class="btn btn-sm rounded-pill px-3 <?= empty($currentStatus) ? 'btn-primary' : 'btn-light border' ?>">All</a>
                    <a href="<?= BASE_URL ?>/pm?status=PENDING" class="btn btn-sm rounded-pill px-3 <?= $currentStatus === 'PENDING' ? 'btn-primary' : 'btn-light border' ?>">Pending</a>
                    <a href="<?= BASE_URL ?>/pm?status=GENERATED" class="btn btn-sm rounded-pill px-3 <?= $currentStatus === 'GENERATED' ? 'btn-primary' : 'btn-light border' ?>">Dispatched</a>
                    <a href="<?= BASE_URL ?>/pm?status=COMPLETED" class="btn btn-sm rounded-pill px-3 <?= $currentStatus === 'COMPLETED' ? 'btn-primary' : 'btn-light border' ?>">Completed</a>
                </div>
            </div>
            <div class="col-12 col-md-5">
                <select name="customer_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="">-- All Customer Accounts --</option>
                    <?php foreach ($customers as $cust): ?>
                        <option value="<?= $cust['id'] ?>" <?= ($currentCustId == $cust['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cust['company_name']) ?> (<?= $cust['customer_code'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 text-md-end">
                <?php if (!empty($currentStatus) || !empty($currentCustId)): ?>
                    <a href="<?= BASE_URL ?>/pm" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i> Reset Filters
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Schedules Table Card -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-secondary text-xs text-uppercase">
                    <th class="ps-3 py-3">Schedule Date</th>
                    <th>Customer Company</th>
                    <th>Asset Code & Model</th>
                    <th>Serial Number</th>
                    <th>Contract #</th>
                    <th>Dispatch Call</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="8" class="text-center p-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark">No PM Schedules Found</h6>
                            <p class="small text-muted mb-3">Click the "Sync PM Schedules" button above to generate periodic schedules for all active AMC contracts.</p>
                            <form method="POST" action="<?= BASE_URL ?>/pm/sync-schedules" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="bi bi-arrow-repeat me-1"></i> Auto-Generate PM Schedules
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $pm): ?>
                        <?php
                        $isDue = ($pm['status'] === 'PENDING' && $pm['schedule_date'] <= date('Y-m-d'));
                        ?>
                        <tr class="<?= $isDue ? 'table-warning table-opacity-25' : '' ?>">
                            <td class="ps-3">
                                <strong class="text-dark"><?= date('d M Y', strtotime($pm['schedule_date'])) ?></strong>
                                <?php if ($isDue): ?>
                                    <span class="badge bg-danger ms-1 text-xxs">Due</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($pm['company_name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fw-bold text-xxs">
                                    <?= $pm['asset_code'] ?>
                                </span>
                                <div class="text-xs text-muted mt-1"><?= htmlspecialchars($pm['make'] . ' ' . $pm['model']) ?></div>
                            </td>
                            <td><code><?= htmlspecialchars($pm['serial_number']) ?></code></td>
                            <td>
                                <a href="<?= BASE_URL ?>/contracts/<?= $pm['contract_id'] ?>" class="text-decoration-none fw-semibold small">
                                    <?= $pm['contract_number'] ?>
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($pm['call_number'])): ?>
                                    <a href="<?= BASE_URL ?>/calls/<?= $pm['call_id'] ?>" class="badge bg-primary text-decoration-none px-2 py-1">
                                        <i class="bi bi-ticket-perforated me-1"></i><?= $pm['call_number'] ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted text-xs">Not Dispatched</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $pm['status'] === 'COMPLETED' ? 'bg-success' : ($pm['status'] === 'GENERATED' ? 'bg-info' : ($isDue ? 'bg-danger' : 'bg-warning text-dark')) ?> rounded-pill px-2 py-1 text-xs">
                                    <?= $pm['status'] ?>
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <?php if ($pm['status'] === 'PENDING'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/pm/generate-single/<?= $pm['id'] ?>" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                                        <button type="submit" class="btn btn-xs btn-primary rounded-pill px-3 shadow-sm fw-bold">
                                            <i class="bi bi-plus-circle me-1"></i> Generate Call
                                        </button>
                                    </form>
                                <?php elseif (!empty($pm['call_id'])): ?>
                                    <a href="<?= BASE_URL ?>/calls/<?= $pm['call_id'] ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-3">
                                        <i class="bi bi-folder2-open me-1"></i> View Ticket
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
