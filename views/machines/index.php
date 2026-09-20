<?php
/**
 * Machine / IT Asset Directory View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Hardware & IT Asset Master</h4>
        <p class="text-muted small mb-0">Track full computer configurations, serial numbers, locations, and AMC warranty status.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/machines/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Register IT Asset
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="fw-bold text-dark"><i class="bi bi-pc-display text-primary me-2"></i>All Registered Assets (<?= count($machines) ?>)</span>
            </div>
            <div class="col-md-6 text-md-end">
                <input type="text" id="filterMachine" class="form-control form-control-sm d-inline-block w-auto" placeholder="Filter by serial, make, client...">
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="machineTable">
            <thead>
                <tr>
                    <th>Asset Code / Tag</th>
                    <th>Type</th>
                    <th>Make & Model</th>
                    <th>Serial Number</th>
                    <th>Customer & Location</th>
                    <th>Specs (CPU/RAM/SSD)</th>
                    <th>AMC Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($machines)): ?>
                    <tr><td colspan="8" class="text-center text-muted p-4">No IT assets registered in system yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($machines as $m): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/machines/<?= $m['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= $m['asset_code'] ?>
                                </a>
                                <?php if (!empty($m['asset_tag'])): ?>
                                    <div class="text-xs text-muted">Tag: <code><?= htmlspecialchars($m['asset_tag']) ?></code></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi <?= $m['asset_type_icon'] ?? 'bi-pc' ?> me-1 text-primary"></i>
                                    <?= htmlspecialchars($m['asset_type_name']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($m['make'] . ' ' . $m['model']) ?></div>
                                <div class="text-xs text-muted">User: <?= htmlspecialchars($m['assigned_employee'] ?? 'Shared') ?></div>
                            </td>
                            <td><code><?= htmlspecialchars($m['serial_number']) ?></code></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($m['company_name']) ?></div>
                                <div class="text-xs text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($m['location_name']) ?> (<?= htmlspecialchars($m['department'] ?? 'General') ?>)</div>
                            </td>
                            <td>
                                <div class="text-xs text-dark fw-semibold"><?= htmlspecialchars($m['processor'] ?? 'Standard CPU') ?></div>
                                <div class="text-xs text-muted"><?= htmlspecialchars($m['ram'] ?? '8GB') ?> | <?= htmlspecialchars($m['storage'] ?? 'SSD') ?></div>
                            </td>
                            <td>
                                <?php if ($m['status'] === 'UNDER_AMC'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-shield-check me-1"></i> AMC Covered
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        Non-AMC
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/machines/<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Asset Profile">
                                    <i class="bi bi-eye"></i>
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
document.getElementById('filterMachine')?.addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#machineTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
