<?php
/**
 * System Settings & Company Parameters View
 */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">System Configuration & Company Settings</h4>
        <p class="text-muted small mb-0">Configure company identity, GST tax defaults, document sequential prefixes, and SLA behavior.</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/settings/update">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-building me-1"></i> Company Identity (Printed on Invoices & Reports)</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Company Name *</label>
                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name'] ?? 'Apex IT Solutions & AMC Services') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Company GSTIN</label>
                    <input type="text" name="company_gstin" class="form-control" value="<?= htmlspecialchars($settings['company_gstin'] ?? '27AAACA1234A1Z5') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Currency Symbol</label>
                    <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '₹') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Official Email</label>
                    <input type="email" name="company_email" class="form-control" value="<?= htmlspecialchars($settings['company_email'] ?? 'support@amc.com') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Phone Number</label>
                    <input type="text" name="company_phone" class="form-control" value="<?= htmlspecialchars($settings['company_phone'] ?? '+91 98765 43210') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Company Website</label>
                    <input type="text" name="company_website" class="form-control" value="<?= htmlspecialchars($settings['company_website'] ?? 'https://amc.local') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Full Registered Address</label>
                    <input type="text" name="company_address" class="form-control" value="<?= htmlspecialchars($settings['company_address'] ?? 'Tower B, Tech Park, Mumbai, MH') ?>">
                </div>
            </div>

            <h6 class="fw-bold text-uppercase fs-8 text-primary mb-3"><i class="bi bi-hash me-1"></i> Document Number Prefixes</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Call Prefix</label>
                    <input type="text" name="call_prefix" class="form-control" value="<?= htmlspecialchars($settings['call_prefix'] ?? 'CALL-') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Contract Prefix</label>
                    <input type="text" name="contract_prefix" class="form-control" value="<?= htmlspecialchars($settings['contract_prefix'] ?? 'CON-') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Asset Prefix</label>
                    <input type="text" name="asset_prefix" class="form-control" value="<?= htmlspecialchars($settings['asset_prefix'] ?? 'AST-') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" class="form-control" value="<?= htmlspecialchars($settings['invoice_prefix'] ?? 'INV-') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Job Card Prefix</label>
                    <input type="text" name="service_report_prefix" class="form-control" value="<?= htmlspecialchars($settings['service_report_prefix'] ?? 'SR-') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Purchase Prefix</label>
                    <input type="text" name="purchase_prefix" class="form-control" value="<?= htmlspecialchars($settings['purchase_prefix'] ?? 'PUR-') ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <button type="submit" class="btn btn-primary px-4 fw-bold">Save System Parameters</button>
            </div>
        </form>
    </div>
</div>
