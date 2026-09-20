<?php
/**
 * AMC Contract Profile & Covered Asset Mapping View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0"><?= htmlspecialchars($contract['title']) ?></h4>
            <span class="badge bg-dark"><?= $contract['contract_number'] ?></span>
            <span class="badge <?= $contract['status'] === 'ACTIVE' ? 'bg-success' : 'bg-secondary' ?>"><?= $contract['status'] ?></span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            <i class="bi bi-building"></i> <?= htmlspecialchars($contract['company_name']) ?> (<?= $contract['customer_code'] ?>) |
            <i class="bi bi-calendar-check"></i> Valid: <strong><?= $contract['start_date'] ?></strong> to <strong><?= $contract['end_date'] ?></strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#mapMachinesModal">
            <i class="bi bi-plus-circle me-1"></i> Map Machines to Contract
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Financials & SLA Policy -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-cash-stack text-success me-2"></i>Commercial & Billing Terms</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Contract Value</label>
                        <div class="fs-5 fw-bold text-dark">₹<?= number_format($contract['contract_value'], 2) ?></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Billing Frequency</label>
                        <div class="fw-semibold text-dark"><?= $contract['billing_frequency'] ?></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">Payment Terms</label>
                        <div class="fw-semibold"><?= htmlspecialchars($contract['payment_terms'] ?? 'Advance') ?></div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted text-xs text-uppercase fw-bold">GST Rate</label>
                        <div class="fw-semibold"><?= $contract['gst_rate'] ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coverage Rules & SLA Policy -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <span class="fw-bold text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Coverage Model & SLA Rules</span>
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded-3 mb-3">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($contract['contract_type_name']) ?></div>
                    <div class="small text-muted mt-1">
                        Labour & Engineer Visits: <strong class="text-success">Covered</strong> | 
                        Spare Parts: <strong class="<?= $contract['is_spares_covered'] ? 'text-success' : 'text-danger' ?>"><?= $contract['is_spares_covered'] ? 'Included (Zero Charge)' : 'Chargeable Extra' ?></strong>
                    </div>
                </div>
                <div class="row g-2 small">
                    <div class="col-6">
                        <i class="bi bi-stopwatch text-primary me-1"></i> Response SLA: <strong><?= $contract['response_time_hrs'] ?> Hours</strong>
                    </div>
                    <div class="col-6">
                        <i class="bi bi-check2-circle text-success me-1"></i> Resolution SLA: <strong><?= $contract['resolution_time_hrs'] ?> Hours</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mapped Machines List -->
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark"><i class="bi bi-pc-display text-primary me-2"></i>Machines Covered Under this Contract (<?= count($contractMachines) ?>)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Device</th>
                    <th>Serial Number</th>
                    <th>Location / Department</th>
                    <th>Contract Period</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contractMachines)): ?>
                    <tr><td colspan="6" class="text-center text-muted p-4">No machines mapped to this contract yet. Click "Map Machines" to attach assets.</td></tr>
                <?php else: ?>
                    <?php foreach ($contractMachines as $cm): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/machines/<?= $cm['machine_id'] ?>" class="fw-bold text-primary">
                                    <?= $cm['asset_code'] ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($cm['make'] . ' ' . $cm['model']) ?></td>
                            <td><code><?= htmlspecialchars($cm['serial_number']) ?></code></td>
                            <td><?= htmlspecialchars($cm['location_name']) ?> (<?= htmlspecialchars($cm['department'] ?? 'General') ?>)</td>
                            <td><span class="text-xs text-muted"><?= $cm['start_date'] ?> to <?= $cm['end_date'] ?></span></td>
                            <td><span class="badge bg-success"><?= $cm['status'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Map Machines Modal -->
<div class="modal fade" id="mapMachinesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>/contracts/<?= $contract['id'] ?>/assign-machines">
                <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">
                <div class="modal-header bg-dark text-white p-3">
                    <h6 class="modal-title fw-bold"><i class="bi bi-pc-display text-primary me-2"></i>Map Client Machines to AMC</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <?php if (empty($unassignedMachines)): ?>
                        <div class="text-center text-muted p-4">All registered assets for this client are already covered under AMC.</div>
                    <?php else: ?>
                        <p class="small text-muted mb-3">Select the machines below to map to <strong><?= $contract['contract_number'] ?></strong>:</p>
                        <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="40"><input type="checkbox" id="selectAllMachines" onclick="toggleSelectAll(this)"></th>
                                        <th>Asset Code</th>
                                        <th>Make & Model</th>
                                        <th>Serial Number</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unassignedMachines as $um): ?>
                                        <tr>
                                            <td><input type="checkbox" name="machine_ids[]" value="<?= $um['id'] ?>" class="machine-checkbox"></td>
                                            <td class="fw-bold"><?= $um['asset_code'] ?></td>
                                            <td><?= htmlspecialchars($um['make'] . ' ' . $um['model']) ?></td>
                                            <td><code><?= htmlspecialchars($um['serial_number']) ?></code></td>
                                            <td><?= htmlspecialchars($um['location_name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <?php if (!empty($unassignedMachines)): ?>
                        <button type="submit" class="btn btn-primary fw-bold">Map Selected Assets</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSelectAll(master) {
    document.querySelectorAll('.machine-checkbox').forEach(cb => cb.checked = master.checked);
}
</script>
