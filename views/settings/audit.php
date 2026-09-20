<?php
/**
 * System Audit Trail Log View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">System Audit Trail & Access Logs</h4>
        <p class="text-muted small mb-0">Traceability of all database inserts, updates, deletions, user IPs, and role actions.</p>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User & Role</th>
                    <th>Action</th>
                    <th>Module / Table</th>
                    <th>Record ID</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center text-muted p-4">No audit logs recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><span class="small text-muted"><?= date('d M Y, h:i:s A', strtotime($log['created_at'])) ?></span></td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></strong>
                                <div class="text-xs text-muted"><?= $log['role_name'] ?? 'Admin' ?></div>
                            </td>
                            <td>
                                <span class="badge <?= $log['action'] === 'INSERT' ? 'bg-success' : ($log['action'] === 'UPDATE' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                    <?= $log['action'] ?>
                                </span>
                            </td>
                            <td><code><?= htmlspecialchars($log['module']) ?></code></td>
                            <td><span class="fw-bold">#<?= $log['record_id'] ?></span></td>
                            <td><span class="text-muted text-xs"><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
