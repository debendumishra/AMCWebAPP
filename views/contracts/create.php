<?php
/**
 * Create AMC Contract View
 */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <span class="p-2 bg-primary-subtle text-primary rounded-3 fs-5 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-file-earmark-plus"></i>
            </span>
            <span>Create Annual Maintenance Contract (AMC)</span>
        </h4>
        <p class="text-muted small mb-0">Define contract terms, coverage type, duration, financial value, and SLA response/resolution guarantees.</p>
    </div>
    <a href="<?= BASE_URL ?>/contracts" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Contracts
    </a>
</div>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="<?= BASE_URL ?>/contracts/create" id="contractForm">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

            <!-- Section 1: Customer & Coverage Type -->
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                <span class="badge bg-primary rounded-circle p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                <h6 class="fw-bold text-dark mb-0 fs-6">Customer & Coverage Plan</h6>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Customer Company / Client <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Choose Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?> (<?= htmlspecialchars($c['customer_code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text text-xs">Contract will be linked to this organization and its asset base.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark">Contract Coverage Model <span class="text-danger">*</span></label>
                    <select name="contract_type_id" class="form-select" required>
                        <?php foreach ($contractTypes as $ct): ?>
                            <option value="<?= $ct['id'] ?>">
                                <?= htmlspecialchars($ct['name']) ?> (Spares: <?= $ct['is_spares_covered'] ? 'Covered' : 'Chargeable' ?> | PM: <?= htmlspecialchars($ct['pm_frequency'] ?? 'Quarterly') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text text-xs">Determines whether spare parts are free of charge or billed.</div>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold text-dark">Contract Agreement Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Comprehensive Annual IT Infrastructure Support Agreement 2026-27">
                </div>
            </div>

            <!-- Section 2: Duration & Billing Terms -->
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom pt-2">
                <span class="badge bg-primary rounded-circle p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                <h6 class="fw-bold text-dark mb-0 fs-6">Tenure, Valuation & Invoicing</h6>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="startDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" id="endDate" class="form-control" value="<?= date('Y-m-d', strtotime('+1 year')) ?>" required>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">Contract Value (INR) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">₹</span>
                        <input type="number" step="0.01" name="contract_value" class="form-control" required placeholder="0.00">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">Billing Schedule</label>
                    <select name="billing_frequency" class="form-select">
                        <option value="ANNUAL">Annual (100% Upfront)</option>
                        <option value="HALF_YEARLY">Half-Yearly (2 Installments)</option>
                        <option value="QUARTERLY" selected>Quarterly (4 Installments)</option>
                        <option value="MONTHLY">Monthly</option>
                    </select>
                </div>
            </div>

            <!-- Section 3: SLA Performance Guarantees -->
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom pt-2">
                <span class="badge bg-primary rounded-circle p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                <h6 class="fw-bold text-dark mb-0 fs-6">SLA Commitments & Taxation</h6>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">SLA Response Time (Hours)</label>
                    <div class="input-group">
                        <input type="number" name="response_time_hrs" class="form-control" value="2" min="1" required>
                        <span class="input-group-text bg-light text-muted text-xs">Hrs</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">SLA Resolution Time (Hours)</label>
                    <div class="input-group">
                        <input type="number" name="resolution_time_hrs" class="form-control" value="8" min="1" required>
                        <span class="input-group-text bg-light text-muted text-xs">Hrs</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">Renewal Reminder Trigger</label>
                    <div class="input-group">
                        <input type="number" name="renewal_reminder_days" class="form-control" value="30" min="1">
                        <span class="input-group-text bg-light text-muted text-xs">Days</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small fw-bold text-dark">GST Rate (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="gst_rate" class="form-control" value="18.00">
                        <span class="input-group-text bg-light text-muted text-xs">%</span>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold text-dark">Special Terms & Operational Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes regarding emergency holiday coverage, backup standby machines, etc."></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                <a href="<?= BASE_URL ?>/contracts" class="btn btn-light border rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">
                    <i class="bi bi-check-circle-fill me-1"></i> Save & Activate Contract
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var startInput = document.getElementById('startDate');
    var endInput = document.getElementById('endDate');

    if (startInput && endInput) {
        startInput.addEventListener('change', function() {
            var startDate = new Date(this.value);
            if (!isNaN(startDate.getTime())) {
                var nextYear = new Date(startDate);
                nextYear.setFullYear(nextYear.getFullYear() + 1);
                var yyyy = nextYear.getFullYear();
                var mm = String(nextYear.getMonth() + 1).padStart(2, '0');
                var dd = String(nextYear.getDate()).padStart(2, '0');
                endInput.value = yyyy + '-' + mm + '-' + dd;
            }
        });
    }
});
</script>

