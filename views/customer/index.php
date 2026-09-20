<?php
/**
 * Customer Self-Service Dashboard View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Welcome, <?= htmlspecialchars($customer['company_name'] ?? 'Client') ?></h4>
        <p class="text-muted small mb-0">Customer Portal: View covered machines, raise repair tickets, and download signed service reports.</p>
    </div>
    <a href="<?= BASE_URL ?>/customer/calls/create" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Raise Repair Ticket
    </a>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <div class="stat-label">Active Support Calls</div>
                <div class="stat-val text-primary"><?= count($openCalls) ?></div>
                <div class="text-xs text-muted mt-1">In progress with technician</div>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-headset"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <div class="stat-label">Registered IT Assets</div>
                <div class="stat-val text-success"><?= count($machines) ?></div>
                <div class="text-xs text-muted mt-1">Computers, Laptops & Servers</div>
            </div>
            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-pc-display"></i></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <div class="stat-label">Contract Status</div>
                <div class="stat-val text-success fs-5">Active AMC</div>
                <div class="text-xs text-muted mt-1">Comprehensive Support</div>
            </div>
            <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-shield-check"></i></div>
        </div>
    </div>
</div>

<!-- Active Tickets Table -->
<div class="card mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark"><i class="bi bi-headset text-primary me-2"></i>Active Service Tickets</span>
        <a href="<?= BASE_URL ?>/customer/calls" class="small text-primary text-decoration-none">View All Calls &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Call Number</th>
                    <th>Reported Issue</th>
                    <th>Assigned Tech</th>
                    <th>Status</th>
                    <th>Date Logged</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($openCalls)): ?>
                    <tr><td colspan="5" class="text-center text-muted p-4">No active service complaints right now. All your machines are running smoothly!</td></tr>
                <?php else: ?>
                    <?php foreach ($openCalls as $c): ?>
                        <tr>
                            <td><strong class="text-primary"><?= $c['call_number'] ?></strong></td>
                            <td><?= htmlspecialchars($c['reported_issue']) ?></td>
                            <td><?= htmlspecialchars($c['engineer_name'] ?? 'Assigning Soon') ?></td>
                            <td><span class="badge-status badge-status-<?= strtolower($c['status']) ?>"><?= $c['status'] ?></span></td>
                            <td><span class="text-muted text-xs"><?= date('d M Y, h:i A', strtotime($c['created_at'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
