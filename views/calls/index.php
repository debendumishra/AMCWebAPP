<?php
/**
 * Service Calls & Dispatch Queue View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Service Desk & Dispatch Queue</h4>
        <p class="text-muted small mb-0">Track breakdown complaints, engineer assignments, live SLA timers, and field resolutions.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/calls/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Log New Call
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/calls" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Statuses --</option>
                    <option value="NEW" <?= $status === 'NEW' ? 'selected' : '' ?>>NEW (Unassigned)</option>
                    <option value="ASSIGNED" <?= $status === 'ASSIGNED' ? 'selected' : '' ?>>ASSIGNED</option>
                    <option value="ON_THE_WAY" <?= $status === 'ON_THE_WAY' ? 'selected' : '' ?>>ON THE WAY</option>
                    <option value="ARRIVED" <?= $status === 'ARRIVED' ? 'selected' : '' ?>>ARRIVED</option>
                    <option value="SPARE_REQUIRED" <?= $status === 'SPARE_REQUIRED' ? 'selected' : '' ?>>SPARE REQUIRED</option>
                    <option value="RESOLVED" <?= $status === 'RESOLVED' ? 'selected' : '' ?>>RESOLVED</option>
                    <option value="CLOSED" <?= $status === 'CLOSED' ? 'selected' : '' ?>>CLOSED</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Priorities --</option>
                    <option value="CRITICAL" <?= $priority === 'CRITICAL' ? 'selected' : '' ?>>CRITICAL (30m SLA)</option>
                    <option value="HIGH" <?= $priority === 'HIGH' ? 'selected' : '' ?>>HIGH (1h SLA)</option>
                    <option value="MEDIUM" <?= $priority === 'MEDIUM' ? 'selected' : '' ?>>MEDIUM (2h SLA)</option>
                    <option value="LOW" <?= $priority === 'LOW' ? 'selected' : '' ?>>LOW</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" id="filterCallQueue" class="form-control form-control-sm" placeholder="Search ticket number, customer, issue...">
            </div>
            <div class="col-md-2 text-end">
                <a href="<?= BASE_URL ?>/calls" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="callsTable">
            <thead>
                <tr>
                    <th>Call Number</th>
                    <th>Type / Priority</th>
                    <th>Customer & Site</th>
                    <th>Asset Details</th>
                    <th>Reported Problem</th>
                    <th>Assigned Tech</th>
                    <th>Status</th>
                    <th>SLA Countdown</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($calls)): ?>
                    <tr><td colspan="9" class="text-center text-muted p-4">No service calls found matching current filters.</td></tr>
                <?php else: ?>
                    <?php foreach ($calls as $c): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/calls/<?= $c['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= $c['call_number'] ?>
                                </a>
                                <div class="text-xs text-muted"><?= date('d M, h:i A', strtotime($c['created_at'])) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= $c['call_type'] ?></span>
                                <span class="badge <?= $c['priority'] === 'CRITICAL' ? 'bg-danger' : ($c['priority'] === 'HIGH' ? 'bg-warning text-dark' : 'bg-info-subtle text-info') ?>">
                                    <?= $c['priority'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($c['company_name']) ?></div>
                                <div class="text-xs text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($c['location_name']) ?></div>
                                <div class="text-xs text-muted">Caller: <?= htmlspecialchars($c['caller_name']) ?> (<a href="tel:<?= $c['caller_mobile'] ?>"><?= $c['caller_mobile'] ?></a>)</div>
                            </td>
                            <td>
                                <?php if (!empty($c['asset_code'])): ?>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($c['make'] . ' ' . $c['model']) ?></div>
                                    <div class="text-xs text-muted"><code><?= htmlspecialchars($c['serial_number']) ?></code></div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">General Hardware</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark"><?= htmlspecialchars($c['problem_name'] ?? 'Hardware Fault') ?></div>
                                <div class="text-xs text-muted"><?= htmlspecialchars(substr($c['reported_issue'], 0, 45)) ?>...</div>
                            </td>
                            <td>
                                <?php if (!empty($c['engineer_name'])): ?>
                                    <div class="fw-semibold text-dark"><i class="bi bi-person-fill text-primary"></i> <?= htmlspecialchars($c['engineer_name']) ?></div>
                                    <div class="text-xs text-muted"><a href="tel:<?= $c['engineer_mobile'] ?>"><?= $c['engineer_mobile'] ?></a></div>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-exclamation-circle me-1"></i>Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-status badge-status-<?= strtolower($c['status']) ?>">
                                    <?= str_replace('_', ' ', $c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'CLOSED' || $c['status'] === 'RESOLVED'): ?>
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check2-all me-1"></i>Completed</span>
                                <?php elseif (!empty($c['sla_resolution_deadline'])): ?>
                                    <div data-sla-deadline="<?= $c['sla_resolution_deadline'] ?>">
                                        <span class="spinner-border spinner-border-sm text-secondary"></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted text-xs">Standard</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/calls/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Call">
                                    <i class="bi bi-arrow-right"></i>
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
document.getElementById('filterCallQueue')?.addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#callsTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
