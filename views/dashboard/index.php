<?php
/**
 * Desktop Operations Dashboard View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">AMC & Field Service Command Center</h4>
        <p class="text-muted small mb-0">Live operations, SLA monitors, active technician dispatches, and inventory status.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/calls/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Log Service Call
        </a>
    </div>
</div>

<!-- KPI Stat Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Active Calls</div>
                <div class="stat-val text-primary"><?= $stats['open_calls'] ?></div>
                <div class="small text-danger fw-semibold mt-1">
                    <i class="bi bi-fire me-1"></i><?= $stats['critical_calls'] ?> Critical Priority
                </div>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
                <i class="bi bi-headset"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">SLA Breaches / Warning</div>
                <div class="stat-val text-danger"><?= $stats['sla_breached'] ?></div>
                <div class="small text-muted mt-1">
                    <i class="bi bi-clock-history me-1"></i>Live Escalation Tracking
                </div>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger">
                <i class="bi bi-exclamation-octagon-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Active AMC Contracts</div>
                <div class="stat-val text-success"><?= $stats['active_contracts'] ?></div>
                <div class="small text-muted mt-1">
                    <i class="bi bi-pc-display me-1"></i><?= $stats['total_machines'] ?> Covered Assets
                </div>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div>
                <div class="stat-label">Spare Approvals & Low Stock</div>
                <div class="stat-val text-warning"><?= $stats['pending_spares'] ?></div>
                <div class="small text-warning fw-semibold mt-1">
                    <i class="bi bi-box-seam me-1"></i><?= $stats['low_stock_items'] ?> Low Inventory Alerts
                </div>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
                <i class="bi bi-boxes"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Live Call Dispatch & SLA Queue -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-activity text-primary me-2"></i>Live Service Call Queue & SLA Monitor</span>
                    <a href="<?= BASE_URL ?>/calls" class="small text-primary text-decoration-none fw-semibold">View All Calls &rarr;</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Call No / Type</th>
                            <th>Customer & Location</th>
                            <th>Machine Asset</th>
                            <th>Assigned Tech</th>
                            <th>Status</th>
                            <th>SLA Countdown</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLiveCalls)): ?>
                            <tr><td colspan="7" class="text-center text-muted p-4">No active service calls in queue.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentLiveCalls as $call): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/calls/<?= $call['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                            <?= $call['call_number'] ?>
                                        </a>
                                        <div>
                                            <span class="badge bg-light text-dark border"><?= $call['call_type'] ?></span>
                                            <span class="badge <?= $call['priority'] === 'CRITICAL' ? 'bg-danger text-white' : ($call['priority'] === 'HIGH' ? 'bg-warning text-dark' : 'bg-info-subtle text-info') ?>">
                                                <?= $call['priority'] ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($call['company_name']) ?></div>
                                        <div class="text-xs text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($call['location_name']) ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($call['asset_code'])): ?>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($call['make'] . ' ' . $call['model']) ?></span>
                                            <div class="text-xs text-muted">S/N: <?= htmlspecialchars($call['serial_number']) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">General Issue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($call['engineer_name'])): ?>
                                            <div class="fw-semibold text-dark"><i class="bi bi-person-fill text-primary"></i> <?= htmlspecialchars($call['engineer_name']) ?></div>
                                            <div class="text-xs text-muted"><a href="tel:<?= $call['engineer_mobile'] ?>" class="text-decoration-none text-muted"><?= $call['engineer_mobile'] ?></a></div>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger"><i class="bi bi-exclamation-circle me-1"></i>Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-status-<?= strtolower($call['status']) ?>">
                                            <?= str_replace('_', ' ', $call['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($call['sla_resolution_deadline'])): ?>
                                            <div data-sla-deadline="<?= $call['sla_resolution_deadline'] ?>">
                                                <span class="spinner-border spinner-border-sm text-secondary"></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/calls/<?= $call['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
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
    </div>

    <!-- Field Engineer Workload & Status -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Field Engineers Status</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($engineers as $eng): ?>
                        <div class="list-group-item p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr($eng['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($eng['name']) ?></div>
                                    <div class="text-xs text-muted"><i class="bi bi-tools"></i> <?= htmlspecialchars(substr($eng['skills'] ?? 'Hardware', 0, 30)) ?>...</div>
                                    <div class="mt-1">
                                        <a href="tel:<?= $eng['mobile'] ?>" class="btn btn-xs btn-outline-secondary py-0 px-2"><i class="bi bi-telephone"></i> Call</a>
                                        <a href="https://wa.me/91<?= $eng['mobile'] ?>" target="_blank" class="btn btn-xs btn-outline-success py-0 px-2"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary rounded-pill"><?= $eng['active_calls_count'] ?> Active</span>
                                <div class="text-xs text-success mt-1"><i class="bi bi-dot"></i> Available</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Low Stock & Expiring Contracts -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="bi bi-box-seam text-warning me-2"></i>Low Stock Inventory Alert</span>
                <a href="<?= BASE_URL ?>/inventory" class="small text-primary text-decoration-none">Manage Stock &rarr;</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Spare Part</th>
                            <th>SKU</th>
                            <th>Current</th>
                            <th>Min Req</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lowStockList)): ?>
                            <tr><td colspan="5" class="text-center text-muted p-3">All spare inventory levels are healthy.</td></tr>
                        <?php else: ?>
                            <?php foreach ($lowStockList as $spare): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($spare['name']) ?></td>
                                    <td><code><?= htmlspecialchars($spare['sku']) ?></code></td>
                                    <td class="text-danger fw-bold"><?= $spare['current_stock'] ?></td>
                                    <td><?= $spare['min_stock_level'] ?></td>
                                    <td class="text-end"><span class="badge bg-danger">Reorder Due</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="bi bi-calendar-event text-danger me-2"></i>Contracts Expiring in 30 Days</span>
                <a href="<?= BASE_URL ?>/contracts" class="small text-primary text-decoration-none">View Contracts &rarr;</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Contract No</th>
                            <th>Customer</th>
                            <th>End Date</th>
                            <th class="text-end">Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($expiringContracts)): ?>
                            <tr><td colspan="4" class="text-center text-muted p-3">No contracts expiring within the next 30 days.</td></tr>
                        <?php else: ?>
                            <?php foreach ($expiringContracts as $c): ?>
                                <tr>
                                    <td><a href="<?= BASE_URL ?>/contracts/<?= $c['id'] ?>" class="fw-bold"><?= $c['contract_number'] ?></a></td>
                                    <td><?= htmlspecialchars($c['company_name']) ?></td>
                                    <td><?= $c['end_date'] ?></td>
                                    <td class="text-end"><span class="badge bg-warning text-dark"><?= $c['days_remaining'] ?> Days</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
