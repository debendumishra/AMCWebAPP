<?php
/**
 * AMC Contracts Directory View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Annual Maintenance Contracts (AMC)</h4>
        <p class="text-muted small mb-0">Track customer contract coverage rules, SLA parameters, billing schedules, and covered assets.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/contracts/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New AMC Contract
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <span class="fw-bold text-dark"><i class="bi bi-file-earmark-text text-primary me-2"></i>Active & Historic AMC Contracts (<?= count($contracts) ?>)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Contract Number / Title</th>
                    <th>Customer Company</th>
                    <th>Coverage Type</th>
                    <th>Period (Start - End)</th>
                    <th>Contract Value</th>
                    <th>Covered Assets</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contracts)): ?>
                    <tr><td colspan="8" class="text-center text-muted p-4">No AMC contracts found.</td></tr>
                <?php else: ?>
                    <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/contracts/<?= $c['id'] ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= $c['contract_number'] ?>
                                </a>
                                <div class="text-xs text-dark fw-semibold"><?= htmlspecialchars($c['title']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($c['company_name']) ?></div>
                                <div class="text-xs text-muted"><?= $c['customer_code'] ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($c['contract_type_name']) ?>
                                </span>
                                <div class="text-xs text-muted mt-1">
                                    Spares: <strong><?= $c['is_spares_covered'] ? 'Included' : 'Chargeable' ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= $c['start_date'] ?> to <?= $c['end_date'] ?></div>
                                <div class="text-xs <?= ($c['days_to_expire'] <= 30) ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= $c['days_to_expire'] ?> days remaining
                                </div>
                            </td>
                            <td class="fw-bold text-dark">₹<?= number_format($c['contract_value'], 2) ?></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= $c['machines_count'] ?> Machines</span></td>
                            <td>
                                <span class="badge <?= $c['status'] === 'ACTIVE' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $c['status'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/contracts/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
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
