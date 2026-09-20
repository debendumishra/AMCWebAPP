<?php
/**
 * Customer Support Calls List View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">My Service Requests & Tickets</h4>
        <p class="text-muted small mb-0">Track breakdown complaints and field technician visit updates in real-time.</p>
    </div>
    <a href="<?= BASE_URL ?>/customer/calls/create" class="btn btn-primary shadow-sm btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Raise New Complaint
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Ticket Number</th>
                    <th>Reported Complaint</th>
                    <th>Device</th>
                    <th>Assigned Technician</th>
                    <th>Status</th>
                    <th>Logged Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($calls)): ?>
                    <tr><td colspan="6" class="text-center text-muted p-4">No support calls logged yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($calls as $c): ?>
                        <tr>
                            <td><strong class="text-primary"><?= $c['call_number'] ?></strong></td>
                            <td><?= htmlspecialchars($c['reported_issue']) ?></td>
                            <td>
                                <?php if (!empty($c['asset_code'])): ?>
                                    <span class="fw-semibold"><?= htmlspecialchars($c['make'] . ' ' . $c['model']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">General Hardware</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($c['engineer_name'] ?? 'Assigning Shortly') ?></td>
                            <td><span class="badge-status badge-status-<?= strtolower($c['status']) ?>"><?= $c['status'] ?></span></td>
                            <td><span class="text-muted text-xs"><?= date('d M Y, h:i A', strtotime($c['created_at'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
